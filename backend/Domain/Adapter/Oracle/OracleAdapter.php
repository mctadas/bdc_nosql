<?php

namespace Domain\Adapter\Oracle;

use Bb4w\Domain\Adapter;
use Bb4w\Domain\UnitOfWork;
use Bb4w\ValueObject\ErrorCode;
use Bb4w\ValueObject\Attributes;
use Domain\Adapter\Oracle\Command\Select as SelectCommand;

/**
 * oracle adapter class.
 * 
 * @package Kompro
 */
class OracleAdapter extends Adapter 
{

	protected $_di;

	/**
	 * how many rows to try and select per command
	 */
	const SELECT_ROWS = 1000;

	/**
	 * how many rows to try and select per command if rows are heavy 
	 */
	const SELECT_HEAVY_ROWS = 100;

	public function __construct() 
	{
		$this->_di = \Zend_Registry::get("di");
	}

	/**
	 * @return \Bb4w\Domain\UnitOfWork 
	 */
	public function proceed() 
	{
		// procesas gali vykti pakankamai ilgai
		set_time_limit(0);

		// taking command (message) from inner queue
		$message = $this->_di->oracleQueue->deque();

		if (empty($message)) {
			return;
		}

		// start time. Need to look how long it took
		$timeStart = microtime(true);

		// calling method and taking a look how long it took
		$time1 = microtime(true);

		try {
			switch ($message['class_name']) {

				case 'Domain\Adapter\Oracle\Command\Select':

					$result = $this->select($message);
					break;

				default:

				case 'Domain\Adapter\Oracle\Command\Export':

					$result = $this->export($message);
					break;

				default:

					$result['errors'][] = "Unknown command " . $message['class_name'];
					break;
			}
		} catch (\InvalidArgumentException $ex) {
			$result['errors'][] = $ex->getMessage();
		}

		// call some shit
		$time2 = microtime(true);

		$timeTookMethodToProcess = $time2 - $time1;
		$success = false;

		// if commands fail with an error, it will be added to the result and sent to _buildEvent generating a failedEvent
		$commands = $this->_buildCommands($message, $result);

		$unitOfWork = new UnitOfWork(
						$this->_buildEvent($message, $result, $success),
						$commands
		);

		// saving events and objects and taking a look how long it took
		$time1 = microtime(true);
		$this->_di->commandDispatcher->saveUnitOfWork($unitOfWork);
		$time2 = microtime(true);
		$timeTookSavingEvents = $time2 - $time1;

		// publishing events and taking a look how long it took
		$time1 = microtime(true);
		foreach ($unitOfWork->getEvents() as $event) {
			$this->_di->commandDispatcher->publishEvent($event);
		}
		$time2 = microtime(true);
		$timeTookPublishingEvents = $time2 - $time1;

		// taking peak memory usage
		$peakUsage = memory_get_peak_usage(true);
		$peakUsage = ($peakUsage / 1048576);

		// delete command from queue
		if ($success) {
			$this->_di->oracleQueue->removeFromQueue($message["identity"]);
		} else {
			$this->_di->oracleQueue->updateFailedQueue($message['identity']);
			// @TODO: jei fail'ina ne pirma karta, increase'int failed
		}

		// need to count total time
		$timeEnd = microtime(true);
		$totalTime = $timeEnd - $timeStart;

		// store all proceed log to system monitor
		$log = array(
			"adapter" => "oracle",
			"message" => $message,
			"time_total" => $totalTime,
			"time_took_method_to_process" => $timeTookMethodToProcess,
			"time_took_saving_events" => $timeTookSavingEvents,
			"time_took_publishing_events" => $timeTookPublishingEvents,
			"events_fired" => $unitOfWork->getEvents(),
			"commands_fired" => count($unitOfWork->getCommands()),
			"peak_memory_usage" => $peakUsage,
		);
		$this->_di->systemMonitorAdapterLogUpdater->log($log);

		return $unitOfWork;
	}

	/**
	 * @param array $data
	 * @return array 
	 */
	public function select(&$data) 
	{
		$response = array(
			'results' => array(),
			'commands' => array()
		);

		$params = (array) $data['attributes'];

		$dsn = 'oci:dbname=//' . $params['host'] . ':' . $params['port'] . '/' . $params['database'];

		try {

			$dbh = new \PDO($dsn, $params['username'], $params['password']);
		} catch (\PDOException $ex) {

			return array('error' => 'apiErrors.oracle.unableToConnect');
		}

		$select_rows = isset($params['select_rows']) ? $params['select_rows'] : self::SELECT_ROWS;

		if (!isset($params['from'])) {

			$stmt = $dbh->query('SELECT COLUMN_NAME, DATA_TYPE FROM "USER_TAB_COLUMNS" WHERE TABLE_NAME=\'' . $params['table'] . '\'');

			if (empty($stmt) || !$stmt instanceof \PDOStatement) {

				return array('error' => 'apiErrors.oracle.unknownTable');
			}

			$columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$available = array();

			// defining wether there will be heavy rows or not
			foreach ($columns as $column) {

				$available[] = $column['COLUMN_NAME'];

				if (!empty($params[$column['COLUMN_NAME']]) && in_array($column['DATA_TYPE'], array('NVARCHAR2', 'VARCHAR2', 'LONG RAW', 'UROWID', 'CLOB', 'NCLOB', 'BLOB', 'BFILE', 'XML Type'))) {

					$select_rows = self::SELECT_HEAVY_ROWS;
				}
			}

			foreach ($params['columns'] as $column => $column_type) {

				if (!in_array($column, $available, true)) {

					return array('error' => 'apiErrors.oracle.unknownColumn');
				}
			}

			$stmt = $dbh->query('SELECT COUNT(*) FROM "' . $params['table'] . '"');

			if (empty($stmt) || !$stmt instanceof \PDOStatement) {

				return array('error' => 'apiErrors.oracle.unknownTable');
			}

			$count = reset($stmt->fetch(\PDO::FETCH_ASSOC));

			// dividing the command into multiple selects
			if ($count > $select_rows) {

				$commandsCount = ceil($count / $select_rows) - 1;
				$command_params = $params;
				$start_from = $select_rows;
				$command_params['select_rows'] = $select_rows;
				$command_params['parent_identity'] = $data['identity'];

				for ($i = 1; $i <= $commandsCount; $i++) {

					$command_params['from'] = $start_from * $i;
					$command_params['saveresult'] = ( ( $i == $commandsCount ) ? 1 : 0 );

					$commandData = array(
						"priority" => 100,
						"adapter" => "oracle",
						"title" => $data['title'],
						"user_id" => $data['user_id'],
						"attributes" => $command_params
					);

					$response['commands'][] = $commandData;
				}
			}

			$params['from'] = 0;
		}

		if (empty($params['from']) && empty($response['commands'])) {

			// no additional commands were queued, so we'll just save the result and move on!
			$data['attributes']->saveresult = 1;
		}

		$columns = '"' . implode('","', array_keys($params['columns'])) . '"';

		// crazy oracle LIMIT with ROWNUM
		$stmt = $dbh->query('SELECT * FROM ( SELECT a.*, ROWNUM rnum FROM ( SELECT ' . $columns . ' FROM "' . $params['table'] . '" ) a 
                              WHERE ROWNUM <= ' . ( $params['from'] + $select_rows ) . ' ) WHERE rnum > ' . $params['from']);

		if (empty($stmt) || !$stmt instanceof \PDOStatement) {

			return array('error' => 'apiErrors.oracle.unableToSelect');
		}

		$response['results'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		return $response;
	}

	/**
	 * @param array $data
	 * @return array 
	 */
	public function export($data) 
	{
		$params = (array) $data['attributes'];

		$response = array(
			'results' => array(),
			'commands' => array()
		);

		$dsn = 'oci:dbname=//' . $params['host'] . ':' . $params['port'] . '/' . $params['database'];

		// establishing a remote connection
		try {

			$remote = new \PDO($dsn, $params['username'], $params['password']);
		} catch (\PDOException $ex) {

			return array('error' => 'apiErrors.oracle.unableToConnect');
		}

		$local = $this->_di->db;

		// selecting results
		$select = $local
				->select()
				->from($params['adapter'] . '_adapter_data', 'COUNT(*)')
				->where("`key` = ?", $params['identity']);

		$count = reset($local->fetchCol($select));

		if ($count == 0) {

			return array('error' => 'apiErrors.zeroresults');
		}

		// checking wether the columns exist + calculating how many rows to insert per query
		$columns = $local
				->select()
				->from($params['adapter'] . '_adapter_meta', array('column', 'type'))
				->where('`key` = ?', $params['identity']);

		$columns = $local->fetchAll($columns);

		// restructuring columns array to array( column => columnType )
		foreach ($columns as $index => $column) {

			unset($columns[$index]);
			$columns[$column['column']] = $column['type'];
		}

		$insertRows = 1000;

		// eaching datamap, checking wether columns exist and checking column type. if type is text, setting a smaller $insertRows
		foreach ($params['datamap'] as $localColumn => $remoteColumn) {

			if (!isset($columns[$localColumn])) {

				return array('error' => 'apiErrors.oracle.unknownLocalColumn');
			}

			if ($columns[$localColumn] == 'text') {

				$insertRows = 200;
			}
		}

		$iterations = ceil(( $count / $insertRows));

		$remote_keys = "\"" . implode("\",\"", array_values($params['datamap'])) . "\"";
		$remote_values = trim(implode(", ", array_fill(0, count($params['datamap']), "?")));

		$insertSQL = "INSERT INTO \"" . $params['table'] . "\" ({$remote_keys}) VALUES({$remote_values})";

		$stmt = $remote->prepare($insertSQL);

		// selecting from local, inserting into remote
		for ($i = 1; $i <= $iterations; $i++) {

			try {

				$localResults = $local
						->select()
						->from($params['adapter'] . '_adapter_data')
						->where("`key` = ?", $params['identity'])
						->limitPage($i, $insertRows);

				$localResults = $local->fetchAll($localResults);
			} catch (\Exception $ex) {

				return array('error' => $ex->getMessage());
			}

			$remote->beginTransaction();

			foreach ($localResults as $resultRow) {

				$rowVO = @unserialize($resultRow['data']);

				if ($rowVO === false && $resultRow['data'] != 'b:0;') {

					return array('error' => 'apiErrors.oracle.unableToUnserialize');

					echo 'Unable to unserialize the result:' . $resultRow['data'] . " ID:" . $resultRow['identity'];
				}

				$insertData = array();

				foreach ($params['datamap'] as $localColumn => $remoteColumn) {

					$insertData[] = $rowVO->attributes->value->$localColumn;
				}

				try {

					$stmt->execute($insertData);
				} catch (\Exception $ex) {

					return array('error' => 'apiErrors.oracle.unableToInsert');
				}
			}

			$remote->commit();
		}

		return $response;
	}

	/**
	 * @param array $message
	 * @param array $result
	 * @return array 
	 */
	protected function _buildCommands(array $message, array &$result = array()) 
	{
		$response = array();

		if (empty($result['commands'])) {

			return $response;
		}

		foreach ($result['commands'] as $commandData) {

			$command = SelectCommand::buildFromRequestData($commandData);

			if (is_array($command)) {

				$result['errors'] = $command;

				return array();
			}

			$response[] = $command;
		}
		return $response;
	}

	/**
	 * @param array $message
	 * @param array $result
	 * @param boolean $success
	 * @return \Domain\Adapter\Oracle\successEvent 
	 */
	protected function _buildEvent($message, array $result, &$success) 
	{
		switch ($message['class_name']) {

			case 'Domain\Adapter\Oracle\Command\Select':

				$successEvent = 'Domain\Adapter\Oracle\Event\Selected';
				$failedEvent = 'Domain\Adapter\Oracle\Event\SelectionFailed';
				$resultVO = 'Domain\Adapter\Oracle\ValueObject\RowsList';

				break;

			case 'Domain\Adapter\Oracle\Command\Export':

				$successEvent = 'Domain\Adapter\Oracle\Event\Exported';
				$failedEvent = 'Domain\Adapter\Oracle\Event\ExportFailed';

				break;

			default:

				// @TODO loginam, kad sudas nutiko, kuris neaisku kaip cia galetu atsidurt
				die('failed event "' . $message['class_name'] . '" build ' . __CLASS__ . ' @ ' . __FILE__ . ':' . __LINE__);
				break;
		}

		// in both (success and error event) message must be attributes VO so here it is.
		try {
			$voMessage = new Attributes($message);
		} catch (\Exception $ex) {
			die('Attributes VO throwed exception: ' . $ex->getMessage());
		}

		if (isset($result['errors']) || isset($result['error'])) {
			// build error event

			try {
				$voErrorCode = new ErrorCode(( isset($result['errors']) ? \ArrayFunctions::implode("\n", $result['errors']) : $result['error']));
			} catch (\Exception $ex) {
				$voErrorCode = new ErrorCode("Internal error: " . $ex->getMessage());
			}

			$event = new $failedEvent($voMessage, $voErrorCode);
			$success = false;
		} else {
			// build success event

			if (!empty($resultVO)) {

				if (empty($result['results'])) {

					$voErrorCode = new ErrorCode('apiErrors.zeroresults');
					$event = new $failedEvent($voMessage, $voErrorCode);
				} else {

					try {
						$voResults = $resultVO::buildFromRequestData($result["results"]);
						$event = new $successEvent($voResults, $voMessage);
					} catch (\Exception $ex) {
						$voErrorCode = new ErrorCode('failed building success event: ' . $ex->getMessage() . ' event: ' . $resultVO . '::buildFromRequestData');
						$event = new $failedEvent($voMessage, $voErrorCode);
					}
				}
			} else {

				// no results were expected (export)
				$event = new $successEvent($voMessage);
			}
			if (empty($voErrorCode)) {

				$success = true;
			}
		}

		return $event;
	}

}