<?php

namespace Domain\Adapter\Normalized;

use Bb4w\ValueObject\Uuid;
use Bb4w\Domain\Adapter;
use Bb4w\Domain\UnitOfWork;
use Bb4w\ValueObject\ErrorCode;
use Bb4w\ValueObject\Attributes;
use Domain\Adapter\Normalized\Command\Save as SaveCommand;

/**
 * pdf adapter class.
 * 
 * @package Kompro
 */
class NormalizedAdapter extends Adapter 
{

	protected $_di;

	const SAVE_ROWS = 100;

	public function __construct() 
	{
		$this->_di = \Zend_Registry::get("di");
	}

	/**
	 * @param array $data
	 * @return array 
	 */
	public function save(&$data) 
	{
		$response = array(
			'results' => array(),
			'commands' => array()
		);

		$params = (array) $data['attributes'];

		$normalizerDb = $this->_di->dbNormalizer;

		if (!isset($params['from'])) {

			try {

				$count = $normalizerDb->select()->from($params['table'], 'COUNT(*)');
				$count = reset($normalizerDb->fetchCol($count));
			} catch (\Exception $ex) {

				return array('error' => $ex->getMessage());
			}

			if ($count == 0) {

				return array('error' => 'apiErrors.zeroresults');
			}

			/* FETCHING COLUMN DATA */
			$newColumns = $metaColumns = $tableColumns = array();

			$colData = $normalizerDb->query('DESCRIBE `' . $params['table'] . '`');
			$colData = $colData->fetchAll(\PDO::FETCH_ASSOC);

			foreach ($colData as $index => $column) {

				$tableName = substr($column['Field'], 0, 36);
				$columnName = substr($column['Field'], 37);

				$tableColumns[$tableName][] = $columnName;

				if (is_numeric($index)) {

					$colData[$column['Field']] = $column;
					unset($colData[$index]);
				}
			}

			$prefixIndex = 0;

			foreach ($tableColumns as $table => $columns) {

				$prefixIndex++;

				foreach ($columns as $column) {

					$prefix = false;

					foreach ($tableColumns as $otherTable => $otherCols) {

						if ($otherTable == $table)
							continue;

						if (in_array($column, $otherCols)) {

							$prefix = true;
						}
					}

					$type = $colData[$table . '_' . $column]['Type'];
					$type = ( strpos($type, '(') !== false ) ? substr($type, 0, strpos($type, '(')) : $type;

					switch ($type) {

						case 'int':
						case 'text':
						case 'varchar':
							break;

						case 'double':
						case 'float':
							$type = 'decimal';
							break;

						case 'date':
						case 'datetime':
							$type = 'date';
							break;

						default:
							$type = 'varchar';
					}

					if ($prefix) {

						$newColumns[$table . '_' . $column] = $prefixIndex . '_' . $column;
						$metaColumns[$prefixIndex . '_' . $column] = $type;
					} else {

						$newColumns[$table . '_' . $column] = $column;
						$metaColumns[$column] = $type;
					}
				}
			}
			/* END OF FETCHING COLUMN DATA */

			// visa info apie columnus dedam i paramsus ir i sekancias komandas, kad su kiekvienos vykdymu nereiktu skaiciuot what is what
			$params['new_columns'] = $newColumns;
			$params['meta_columns'] = $metaColumns;

			if ($count > self::SAVE_ROWS) {

				$commandsCount = ceil($count / self::SAVE_ROWS) - 1;
				$command_params = $params;
				$start_from = self::SAVE_ROWS;
				$command_params['select_rows'] = self::SAVE_ROWS;
				$command_params['parent_identity'] = $data['identity'];

				for ($i = 1; $i <= $commandsCount; $i++) {

					$command_params['from'] = $start_from * $i;
					$command_params['saveresult'] = ( ( $i == $commandsCount ) ? 1 : 0 );

					$commandData = array(
						"priority" => 100,
						"adapter" => "normalized",
						"title" => $data['title'],
						"user_id" => $data['user_id'],
						"attributes" => $command_params
					);

					$response['commands'][] = $commandData;
				}
			}

			$params['from'] = 0;
		}

		$normalizedData = $normalizerDb->select()->from($params['table'])->limit(self::SAVE_ROWS, $params['from']);
		$normalizedData = $normalizerDb->fetchAll($normalizedData);

		foreach ($normalizedData as $index => $row) {

			foreach ($row as $column => $value) {

				$newColumn = $params['new_columns'][$column];

				$response['results'][$index][$newColumn] = $value;
			}
		}

		if (empty($params['from']) && empty($response['commands'])) {

			// no additional commands were queued, so we'll just save the result and move on!
			$data['attributes']->saveresult = 1;
			$data['attributes']->new_columns = $params['new_columns'];
			$data['attributes']->meta_columns = $params['meta_columns'];
		}

		if (empty($response['results'])) {

			return array('error' => 'apiErrors.zeroresults');
		}

		return $response;
	}

	/**
	 * @return \Bb4w\Domain\UnitOfWork 
	 */
	public function proceed() 
	{
		// procesas gali vykti pakankamai ilgai
		set_time_limit(0);

		// taking command (message) from inner queue
		$message = $this->_di->normalizedQueue->deque();

		if (empty($message)) {

			return;
		}

		// start time. Need to look how long it took
		$timeStart = microtime(true);

		// calling method and taking a look how long it took
		$time1 = microtime(true);

		try {
			switch ($message['class_name']) {

				case 'Domain\Adapter\Normalized\Command\Save':

					$result = $this->save($message);
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
			$this->_di->normalizedQueue->removeFromQueue($message["identity"]);
		} else {
			$this->_di->normalizedQueue->updateFailedQueue($message['identity']);
			// @TODO: jei fail'ina ne pirma karta, increase'int failed
		}

		// need to count total time
		$timeEnd = microtime(true);
		$totalTime = $timeEnd - $timeStart;

		// store all proceed log to system monitor
		$log = array(
			"adapter" => "normalized",
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

		// @TODO masyvo indexai - komandos klasė ar pan., kad nebūt hardkodinta komanda
		foreach ($result['commands'] as $commandData) {

			$command = SaveCommand::buildFromRequestData($commandData);

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
	 * @return \Domain\Adapter\Normalized\successEvent 
	 */
	protected function _buildEvent($message, array $result, &$success) 
	{
		switch ($message['class_name']) {

			case 'Domain\Adapter\Normalized\Command\Save':

				$successEvent = 'Domain\Adapter\Normalized\Event\Saved';
				$failedEvent = 'Domain\Adapter\Normalized\Event\SaveFailed';
				$resultVO = 'Domain\Adapter\Normalized\ValueObject\RowsList';

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
					} catch (\Exception $ex) {
						$voErrorCode = new ErrorCode('failed building success event: ' . $ex->getMessage . ' event: ' . $events['result'] . '::buildFromRequestData');
						$event = new $failedEvent($voMessage, $voErrorCode);
					}

					$event = new $successEvent($voResults, $voMessage);
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