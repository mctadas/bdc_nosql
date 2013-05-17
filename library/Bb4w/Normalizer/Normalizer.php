<?php

namespace Bb4w\Normalizer;

use Bb4w\Normalizer\Command\NormalizeData;
use Bb4w\Normalizer\Command\NormalizeSimpleData;
use \Zend_Db_Adapter_Abstract;

class Normalizer
{
	protected $_db;
	
	protected $_dbNormalizer;
	
	public function __construct(
		Zend_Db_Adapter_Abstract $db,
		Zend_Db_Adapter_Abstract $dbNormalizer
	){
		$this->_db = $db;
		$this->_dbNormalizer = $dbNormalizer;
	}
	
	public function normalize_simple( NormalizeSimpleData $command ) {
		
		$table   = $command->table;
		$adapter = $command->adapter;
		
		$meta = $this->_db->select()->from( $adapter . '_adapter_meta' )->where( "`key` = ?", $table )->query()->fetchAll();
		$data = $this->_db->select()->from( $adapter . '_adapter_data' )->where( "`key` = ?", $table )->query()->fetchAll();
		
		$columns = $this->_createTable( $meta );
		$this->_insertData( $table, $data, $columns );
		
		return 'ok';
	}
	
	public function normalize( NormalizeData $command )
	{
		// creating tables
		$columns = array();
		foreach($command->data->elements as $table) {
			$select = $this->_db
					->select()
					->from($table->metaTableName)
					->where("`key` = ?", $table->key);
			$metaData = $this->_db->fetchAll($select);
			$columns[$table->key] = $this->_createTable($metaData);
		}

		// inserting data
		foreach($command->data->elements as $table) {
			$select = $this->_db
				->select()
				->from($table->dataTableName)
				->where("`key` = ? ", $table->key);
			$data = $this->_db->fetchAll($select);
			
			$this->_insertData( $table->key, $data, $columns[$table->key] );
		}
		
		// joining data.
		$tableName = $this->_createJoinedTable($command->select, $columns);
		$this->_joinTables($command->join, $command->select, $tableName);

		return $tableName;
	}
	
	private function _createJoinedTable($select, $columns)
	{		
		$tableName = md5(implode("|", array_keys($select)));
		$joinedTableColumns = array();
		
		foreach($select as $key => $value) {
			foreach($value["select"] as $v) {
				$joinedTableColumns[] = "`{$key}_{$v}` {$this->_columnsSyntax[$columns[$key][$v]]}";
			}
			foreach($value["sum"] as $v) {
				$joinedTableColumns[] = "`{$key}_{$v}_sum` {$this->_columnsSyntax[$columns[$key][$v]]}";
			}
			foreach($value["count"] as $v) {
				$joinedTableColumns[] = "`{$key}_{$v}_count` {$this->_columnsSyntax[$columns[$key][$v]]}";
			}
		}
		
		$tableStart = "DROP TABLE IF EXISTS `{$tableName}`;";
		$tableStart .= "CREATE TABLE IF NOT EXISTS `{$tableName}` (";
		$tableEnd = ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$sqlSyntax = $tableStart . implode(", ", $joinedTableColumns) . $tableEnd;
		
		$this->_dbNormalizer->query($sqlSyntax);
		
		return $tableName;
	}
	
	private function _joinTables($join, $select, $tableName)
	{
		set_time_limit(0);
		
		// populate table
		$tables = array_keys($join["columns"]);
		if (count($tables) != 2)
			throw new \Exception("where must be 2 tables");
		
		$mainTable = reset($tables);
		$secondTable = reset(array_reverse($tables));
		
		$sqlSelect = array();
		$columns = array();
		foreach($select as $k => $v) {
			foreach($v["select"] as $column) {
				$sqlSelect[] = " `{$k}`.`{$column}` AS `{$k}_{$column}`  ";
				$columns[] = $k . "_" . $column;
			}
			foreach($v["sum"] as $column) {
				$sqlSelect[] = " SUM(`{$k}`.`{$column}`) AS `{$k}_{$column}_sum`  ";
				$columns[] = $k . "_" . $column . "_sum";
			}
			foreach($v["count"] as $column) {
				$sqlSelect[] = " COUNT(`{$k}`.`{$column}`) AS `{$k}_{$column}_count`  ";
				$columns[] = $k . "_" . $column . "_count";
			}
		}
		
		$sqlSelect = implode( " , ", $sqlSelect);
		
		$sql = "
			SELECT
				%select%
			FROM
				`{$mainTable}`
			LEFT JOIN
				`{$secondTable}`
			";
		
		switch($join["column_type"]) {
			case "date":
				$sql .= "
						ON DATE_FORMAT(`{$mainTable}`.`{$join["columns"][$mainTable][0]}`, '{$join["join_by"]}' ) = DATE_FORMAT(`{$secondTable}`.`{$join["columns"][$secondTable][0]}`, '{$join["join_by"]}' )
					";
				break;
			case "int":
				$sql  .= "
						ON `{$mainTable}`.`{$join["columns"][$mainTable][0]}` = `{$secondTable}`.`{$join["columns"][$secondTable][0]}`
					";
				break;
			case "varchar":
				$sql  .= "
						ON `{$mainTable}`.`{$join["columns"][$mainTable][0]}` = `{$secondTable}`.`{$join["columns"][$secondTable][0]}`
					";
				break;
			default:
				die("PANIC IN " . __METHOD__);
				break;
		}
		
		$sqlMain = str_replace("%select%", $sqlSelect, $sql);
		
		$columns = implode("`,`", $columns);
		$sql = "
			INSERT
			INTO
			`{$tableName}` (`{$columns}`)
			{$sqlMain}
			";

		$this->_dbNormalizer->exec($sql);
		
		$dropTablesSql = "
			DROP TABLE IF EXISTS `{$mainTable}`;
			DROP TABLE IF EXISTS `{$secondTable}`;
			";
		$this->_dbNormalizer->exec($dropTablesSql);
	}
	
	private function _insertData($key, $data, $columns)
	{
		$dateColumn = null;
		foreach($columns as $k => $v) {
			if ($v == "date") {
				$dateColumn = $k;
				break;
			}
		}
        
		$insertData = array();
		foreach($data as $row) {
			$result = $this->_parseData(unserialize($row["data"]));
			if (array_key_exists($dateColumn, $result)) {
				$result[$dateColumn] = date("Y-m-d H:i:s", strtotime($result[$dateColumn]));
			}
            $result = \ArrayFunctions::lowerKeys( $result );
			$insertData[] = $result;
		}
        $insertData = array_chunk($insertData, 500);
		
		$columnsString = "`" . implode("`,`", array_keys($columns)) . "`";
		$sql = "INSERT INTO `{$key}` ({$columnsString}) VALUES ";
		
        foreach( $insertData as $dataChunks ) {
			
            $queryVals = array();
            
			foreach( $dataChunks as $row ) {
				
                $queryData = array();
                    
                foreach($columns as $column => $type) {
                    
                    // facebook ne kiekvienoj zinutej buna visi laukai is meta
					$queryData[ $column ] = isset( $row[ $column ] ) ? $this->_dbNormalizer->quote( $row[ $column ] ) : "''";
				}
                
				$queryVals[] = "( " . implode(",", $queryData ) . " )";
			}
			if (!empty($queryVals)) {
                $this->_dbNormalizer->exec($sql . implode(" , ", $queryVals));
			}
		}
	}
	
	private function _parseData($data)
	{
		$return = array();
		
		$className = get_class($data);
		
		switch ($className) {
			case "Domain\Adapter\Facebook\ValueObject\Mention":
				/* @var $data \Domain\Adapter\Facebook\ValueObject\Mention */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\FilesCsvTxt\ValueObject\Row":
				/* @var $data \Domain\Adapter\FilesCsvTxt\ValueObject\Row */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\FilesXlsx\ValueObject\Row":
				/* @var $data \Domain\Adapter\FilesXlsx\ValueObject\Row */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\GooglePlus\ValueObject\Comment":
				/* @var $data \Domain\Adapter\GooglePlus\ValueObject\Comment */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\GooglePlus\ValueObject\Mention":
				/* @var $data \Domain\Adapter\GooglePlus\ValueObject\Mention */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\GooglePlus\ValueObject\User":
				/* @var $data \Domain\Adapter\GooglePlus\ValueObject\User */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\MySQL\ValueObject\Row":
				/* @var $data \Domain\Adapter\MySQL\ValueObject\Row */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\Oracle\ValueObject\Row":
				/* @var $data \Domain\Adapter\Oracle\ValueObject\Row */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\PostgreSQL\ValueObject\Row":
				/* @var $data \Domain\Adapter\PostgreSQL\ValueObject\Row */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\Soap\ValueObject\Item":
				/* @var $data \Domain\Adapter\Soap\ValueObject\Item */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\Twitter\ValueObject\Mention":
				/* @var $data \Domain\Adapter\Twitter\ValueObject\Mention */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\Twitter\ValueObject\Trend":
				/* @var $data \Domain\Adapter\Twitter\ValueObject\Trend */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			case "Domain\Adapter\Normalized\ValueObject\Row":
				/* @var $data \Domain\Adapter\Twitter\ValueObject\Trend */
				$result = $data->attributes->value;
				$return = get_object_vars($result);
				break;
			default:
				die("PANIC IN " . __METHOD__);
				break;
		}
		
		return $return;
	}
	
	
	private $_columnsSyntax = array(
		"varchar" => "VARCHAR(255) DEFAULT NULL",
		"date" => "DATETIME DEFAULT NULL",
		"int" => "int(10) DEFAULT NULL",
		"decimal" => "decimal(20,6) DEFAULT NULL",
		"text" => "TEXT DEFAULT NULL",
	);
	
	private function _createTable($metaData)
	{
		$tableName = null;
		$columns = array();
		$return = array();
		
		foreach($metaData as $row) {
			if (empty($tableName))
				$tableName = $row["key"];
			
			$columns[] = "`{$row['column']}` {$this->_columnsSyntax[$row["type"]]}";
			$return[$row["column"]] = $row["type"];
		}
		
		$tableStart = "CREATE TABLE IF NOT EXISTS `{$tableName}` (";
		$tableEnd = ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$sqlSyntax = $tableStart . implode(", ", $columns) . $tableEnd;
		
		$this->_dbNormalizer->query($sqlSyntax);
		
		return $return;
	}
}