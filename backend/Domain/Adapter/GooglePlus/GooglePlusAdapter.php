<?php

namespace Domain\Adapter\GooglePlus;

use Bb4w\Service\GooglePlus\API;
use Bb4w\Domain\Adapter;
use Bb4w\Domain\UnitOfWork;
use Bb4w\ValueObject\ErrorCode;
use Bb4w\ValueObject\Attributes;

/**
 * GooglePlus adapter class.
 * 
 * @package Kompro
 */
class GooglePlusAdapter extends Adapter 
{

	protected $_di;

	/**
	 * @var Bb4w\Service\GooglePlus\API 
	 */
	public $_api;

	/**
	 * @param string $method
	 * @param array $params
	 * @return array
	 * @throws \InvalidArgumentException 
	 */
	public function _api($method, $params) 
	{
		if (!method_exists($this->_api, $method)) {

			throw new \InvalidArgumentException('apiErrors.googleplus.invalidApiMethod');
		}

		$result = call_user_func(array($this->_api, $method), $params);

		if (isset($result['items'])) {

			return array('results' => $result['items']);
		}

		return $result;
	}

	public function __construct() 
	{
		$this->_di = \Zend_Registry::get("di");
		$this->_api = new API();
	}

	/**
	 * @return \Bb4w\Domain\UnitOfWork 
	 */
	public function proceed() 
	{
		// procesas gali vykti pakankamai ilgai
		set_time_limit(0);

		// taking command (message) from inner queue
		$message = $this->_di->googlePlusQueue->deque();
		if (empty($message)) {
			return;
		}

		// start time. Need to look how long it took
		$timeStart = microtime(true);

		// calling method and taking a look how long it took
		$time1 = microtime(true);

		try {
			switch ($message['class_name']) {

				case 'Domain\Adapter\GooglePlus\Command\FindMention':

					$result = $this->_api('activities', (array) $message['attributes']);
					break;

				case 'Domain\Adapter\GooglePlus\Command\RetrieveComments':

					$result = $this->_api('comments', (array) $message['attributes']);
					break;

				case 'Domain\Adapter\GooglePlus\Command\FindUsers':

					$result = $this->_api('people', (array) $message['attributes']);
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

		$unitOfWork = new UnitOfWork(
						$this->_buildEvent($message, $result, $success),
						array()
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
			$this->_di->googlePlusQueue->removeFromQueue($message["identity"]);
		} else {
			$this->_di->googlePlusQueue->updateFailedQueue($message['identity']);
			// @TODO: jei fail'ina ne pirma karta, increase'int failed
		}

		// need to count total time
		$timeEnd = microtime(true);
		$totalTime = $timeEnd - $timeStart;

		// store all proceed log to system monitor
		$log = array(
			"adapter" => "googleplus",
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
	 * @param boolean $success
	 * @return \Domain\Adapter\GooglePlus\successEvent 
	 */
	protected function _buildEvent($message, array $result, &$success) 
	{
		switch ($message['class_name']) {

			case 'Domain\Adapter\GooglePlus\Command\FindMention':

				$successEvent = 'Domain\Adapter\GooglePlus\Event\MentionFound';
				$failedEvent = 'Domain\Adapter\GooglePlus\Event\MentionFoundFailed';
				$resultVO = 'Domain\Adapter\GooglePlus\ValueObject\MentionsList';

				break;

			case 'Domain\Adapter\GooglePlus\Command\RetrieveComments':

				$successEvent = 'Domain\Adapter\GooglePlus\Event\CommentsRetrieved';
				$failedEvent = 'Domain\Adapter\GooglePlus\Event\CommentsRetrievationFailed';
				$resultVO = 'Domain\Adapter\GooglePlus\ValueObject\CommentsList';

				break;

			case 'Domain\Adapter\GooglePlus\Command\FindUsers':

				$successEvent = 'Domain\Adapter\GooglePlus\Event\UsersFound';
				$failedEvent = 'Domain\Adapter\GooglePlus\Event\UsersFoundFailed';
				$resultVO = 'Domain\Adapter\GooglePlus\ValueObject\UsersList';

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
				$voErrorCode = new ErrorCode(( isset($result['errors']) ? \ArrayFunctions::implode("\n", $result['errors']) : \ArrayFunctions::implode("\n", $result['error'])));
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