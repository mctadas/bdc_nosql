<?php

namespace Domain\Adapter\Twitter;

use Bb4w\Zend\Service\Twitter\Search;
use Bb4w\Zend\Service\Twitter\Trends;
use Bb4w\Zend\Service\Twitter\Tweet;
use Bb4w\Domain\Adapter;
use Bb4w\Domain\UnitOfWork;
use Bb4w\ValueObject\ErrorCode;
use Bb4w\ValueObject\Attributes;

/**
 * Twitter adapter class. Does all shit with twitter 
 * 
 * @package Kompro
 */
class TwitterAdapter extends Adapter 
{
	/**
	 * OAuth consumer keys
	 */
	const CONSUMER_KEY = 'aHJVe05ethjMAM19bhPi6A';
	const CONSUMER_SECRET = 'U2b1aUwR0q27bbWsfskahPFgfNoHMNGHJNZkkOqQM';

	/**
	 * @var Zend_Service_Twitter_Search
	 */
	public $_search;

	/**
	 * @var Bb4w\Zend\Service\Twitter\Trends 
	 */
	public $_trends;
	
	protected $_di;

	public function __construct() 
	{
		$this->_search = new Search("json");
		$this->_trends = new Trends("json");
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
		$message = $this->_di->twitterQueue->deque();

		if (empty($message)) {
			return;
		}

		// start time. Need to look how long it took
		$timeStart = microtime(true);

		// calling method and taking a look how long it took
		$time1 = microtime(true);

		try {
			switch ($message['class_name']) {

				case 'Domain\Adapter\Twitter\Command\FindMention':

					$result = $this->_search((array) $message['attributes']);
					break;

				case 'Domain\Adapter\Twitter\Command\ReceiveTrends':

					$result = $this->_trends((array) $message['attributes']);
					break;

				case 'Domain\Adapter\Twitter\Command\CreateTweet':

					$result = $this->_tweet((array) $message['attributes']);
					break;

				default:

					$result['errors'][] = "Unknown command " . $message['class_name'];
					break;
			}
		} catch (\Exception $ex) {
			$result['errors'][] = $ex->getMessage();
		}

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
			$this->_di->twitterQueue->removeFromQueue($message["identity"]);
		} else {
			$this->_di->twitterQueue->updateFailedQueue($message['identity']);
			// @TODO: jei fail'ina ne pirma karta, increase'int failed
		}

		// need to count total time
		$timeEnd = microtime(true);
		$totalTime = $timeEnd - $timeStart;

		// store all proceed log to system monitor
		$log = array(
			"adapter" => "twitter",
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
	 * @param array $params
	 * @return array 
	 */
	protected function _search($params) 
	{
		$query = $params['query'];
		unset($params['query']);

		$result = $this->_search->search($query, $params);
		return $result;
	}

	/**
	 * @param array $params
	 * @return array 
	 */
	protected function _trends($params) 
	{
		$result = $this->_trends->search($params);
		$result['results'] = isset($result['trends']) ? $result['trends'] : array();
		unset($result['trends']);

		return $result;
	}

	/**
	 * @param array $params
	 * @return array 
	 */
	protected function _tweet($params) 
	{
		$result = Tweet::post(self::CONSUMER_KEY, self::CONSUMER_SECRET, $params);

		return $result;
	}
	
	/**
	 * @param array $message
	 * @param array $result
	 * @param boolean $success
	 * @return \Domain\Adapter\Twitter\successEvent 
	 */
	protected function _buildEvent($message, array $result, &$success) 
	{
		switch ($message['class_name']) {

			case 'Domain\Adapter\Twitter\Command\FindMention':

				$successEvent = 'Domain\Adapter\Twitter\Event\MentionFound';
				$failedEvent = 'Domain\Adapter\Twitter\Event\MentionFoundFailed';
				$resultVO = 'Domain\Adapter\Twitter\ValueObject\MentionsList';

				break;

			case 'Domain\Adapter\Twitter\Command\ReceiveTrends':

				$successEvent = 'Domain\Adapter\Twitter\Event\TrendsReceived';
				$failedEvent = 'Domain\Adapter\Twitter\Event\TrendsReceivationFailed';
				$resultVO = 'Domain\Adapter\Twitter\ValueObject\TrendsList';

				break;

			case 'Domain\Adapter\Twitter\Command\CreateTweet':

				$successEvent = 'Domain\Adapter\Twitter\Event\TweetCreated';
				$failedEvent = 'Domain\Adapter\Twitter\Event\TweetCreationFailed';

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