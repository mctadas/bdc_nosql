<?php

namespace Domain\Adapter\Soap;

use Bb4w\ValueObject\Uuid;
use Bb4w\Domain\Adapter;
use Bb4w\Domain\UnitOfWork;
use Bb4w\ValueObject\ErrorCode;
use Bb4w\ValueObject\Attributes;

/**
 * soap adapter class.
 * 
 * @package Kompro
 */
class SoapAdapter extends Adapter 
{

	protected $_di;

	public function __construct() 
	{
		ini_set("soap.wsdl_cache_enabled", "0");
		$this->_di = \Zend_Registry::get("di");
	}

	/**
	 * @param array $data
	 * @return array 
	 */
	public function export($data) 
	{
		$response = array(
			'results' => array()
		);

		$params = (array) $data['attributes'];
		$options = array(
			'compression' => SOAP_COMPRESSION_ACCEPT,
			'location' => $params['server'],
			'uri' => $params['server']
		);

		if (isset($params['username'])) {

			$options['login'] = $params['username'];
		}

		if (isset($params['password'])) {

			$options['password'] = $params['password'];
		}

		try {

			$client = new \Zend_Soap_Client(null, $options);
		} catch (\Exception $ex) {

			return array('error' => $ex->getMessage());
		}

		$local = $this->_di->db;

		// selecting results
		$select = $local
				->select()
				->from($params['adapter'] . '_adapter_data', 'COUNT(*)')
				->where("`key` = ?", $params['identity']);

		$count = reset($local->fetchCol($select));

		if ($count == 0) {

			return array('error' => 'zero results found');
		}

		$iterations = ceil(( $count / 100));

		// checking wether the columns exist
		$columns = $local
				->select()
				->from($params['adapter'] . '_adapter_meta', 'column')
				->where('`key` = ?', $params['identity']);

		$columns = $local->fetchCol($columns);

		foreach ($params['datamap'] as $localColumn => $remoteColumn) {

			if (!in_array($localColumn, $columns, true)) {

				return array('error' => 'apiErrors.soap.unknownLocalColumn');
			}
		}

		for ($i = 1; $i <= $iterations; $i++) {

			try {

				$localResults = $local
						->select()
						->from($params['adapter'] . '_adapter_data')
						->where("`key` = ?", $params['identity'])
						->limitPage($i, 100);

				$localResults = $local->fetchAll($localResults);
			} catch (\Exception $ex) {

				return array('error' => $ex->getMessage());
			}

			$resultData = array();

			foreach ($localResults as $index => $resultRow) {

				$rowVO = @unserialize($resultRow['data']);

				if ($rowVO === false && $resultRow['data'] != 'b:0;') {

					return array('error' => 'apiErrors.soap.unableToUnserialize');
					echo 'Unable to unserialize the result:' . $resultRow['data'] . " ID:" . $resultRow['identity'];
				}

				foreach ($params['datamap'] as $localColumn => $remoteColumn) {

					$resultData[$index][$remoteColumn] = $rowVO->attributes->value->$localColumn;
				}
			}

			try {

				$response['results'] = call_user_func_array(array($client, $params['ws_method']), array($resultData));
			} catch (\Exception $ex) {

				return array('error' => "ZendSoapClient: " . $ex->getMessage());
			}
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array 
	 */
	public function query(&$data) 
	{
		$response = array(
			'commands' => array(),
			'results' => array()
		);

		$params = (array) $data['attributes'];
		$options = array(
			'compression' => SOAP_COMPRESSION_ACCEPT,
			'location' => $params['server'],
			'uri' => $params['server']
		);

		if (isset($params['username'])) {

			$options['login'] = $params['username'];
		}

		if (isset($params['password'])) {

			$options['password'] = $params['password'];
		}

		try {

			$client = new \Zend_Soap_Client(null, $options);
		} catch (\Exception $ex) {

			return array('error' => $ex->getMessage());
		}

		try {

			$response['results'] = call_user_func(array($client, $params['ws_method']));
		} catch (\Exception $ex) {

			return array('error' => $ex->getMessage());
		}

		if (!is_array($response['results'])) {

			$response['results'] = array($response['results']);
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
		$message = $this->_di->soapQueue->deque();

		if (empty($message)) {

			return;
		}

		// start time. Need to look how long it took
		$timeStart = microtime(true);

		// calling method and taking a look how long it took
		$time1 = microtime(true);

		try {
			switch ($message['class_name']) {

				case 'Domain\Adapter\Soap\Command\Query':

					$result = $this->query($message);
					break;

				case 'Domain\Adapter\Soap\Command\Export':

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

		// if error occurs during command build, $result will contain error message for the event build.
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
			$this->_di->soapQueue->removeFromQueue($message["identity"]);
		} else {
			$this->_di->soapQueue->updateFailedQueue($message['identity']);
			// @TODO: jei fail'ina ne pirma karta, increase'int failed
		}

		// need to count total time
		$timeEnd = microtime(true);
		$totalTime = $timeEnd - $timeStart;

		// store all proceed log to system monitor
		$log = array(
			"adapter" => "soap",
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

		foreach ($result['commands'] as $commandData) {

			$command = ParseFileCommand::buildFromRequestData($commandData);

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
	 * @return \Domain\Adapter\Soap\successEvent 
	 */
	protected function _buildEvent($message, array $result, &$success) 
	{
		switch ($message['class_name']) {

			case 'Domain\Adapter\Soap\Command\Query':

				$successEvent = 'Domain\Adapter\Soap\Event\Queried';
				$failedEvent = 'Domain\Adapter\Soap\Event\QueryFailed';
				$resultVO = 'Domain\Adapter\Soap\ValueObject\Response';

				break;

			case 'Domain\Adapter\Soap\Command\Export':

				$successEvent = 'Domain\Adapter\Soap\Event\Exported';
				$failedEvent = 'Domain\Adapter\Soap\Event\ExportFailed';

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