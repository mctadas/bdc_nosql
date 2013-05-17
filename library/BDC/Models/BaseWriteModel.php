<?php

namespace BDC\Models;

use BDC\ValueObject\Attributes;
use BDC\ValueObject\Uuid;

/**
 * @package Kompro 
 */
abstract class BaseWriteModel extends BaseModel 
{

	/**
	 * @param string $table
	 * @param string $eventKey
	 * @param string $column
	 * @param string $column_type
	 */
	public function insertMeta($table, $eventKey, $column, $column_type) 
	{
		if (!in_array($column_type, array('int', 'decimal', 'text', 'varchar', 'date'))) {

			return;
		}

		$this->_db->insert($table, array(
			'key' => $eventKey,
			'column' => strtolower($column),
			'type' => $column_type
		));
	}

	/**
	 * @param string $table
	 * @param string $eventKey
	 * @param array $data 
	 */
	public function insertData($table, $eventKey, $data) 
	{
		$sql = "INSERT INTO `" . $table . "` (`identity`, `added`, `data`, `key`) VALUES";

		$this->_db->query('SET NAMES utf8');
		$this->_db->query('SET CHARACTER SET utf8');

		if (!is_array($data)) {

			$data = array($data);
		}

		$chunks = array_chunk($data, 100);

		foreach ($chunks as $chunkData) {

			$values = array();

			foreach ($chunkData as $item) {

				$dataIdentifier = ( isset($item->identity->value) ) ? $item->identity->value : Uuid::generateNewUuid();

				$insertData = array(
					$this->_db->quote($dataIdentifier), // identity (row identity)
					$this->_db->quote(date("Y-m-d H:i:s")), // added
					$this->_db->quote(serialize($item)), // data
					$this->_db->quote($eventKey), // key (results key)
				);

				$values[] = "(" . implode(",", $insertData) . ")\n";
			}

			$this->_db->query($sql . implode(',', $values));
		}
	}

	/**
	 * @param string $table
	 * @param string $eventKey
	 * @param array $attributes 
	 */
	public function insertResult($table, $eventKey, $attributes) 
	{
		$this->_db->insert($table, array(
			'key' => $eventKey,
			'user_id' => $attributes->user_id,
			'title' => $attributes->title,
			'command_string' => $attributes->class_name
		));
	}

}