<?php

namespace Domain\Adapter\FilesCsvTxt;

use Bb4w\Domain\Adapter;
use Bb4w\Domain\UnitOfWork;
use Bb4w\ValueObject\ErrorCode;
use Bb4w\ValueObject\Attributes;
use Bb4w\ValueObject\Uuid;
use Domain\Adapter\FilesCsvTxt\Command\ParseFile as ParseFileCommand;

/**
 * filescsvtxt adapter class.
 * 
 * @package Kompro
 */
class FilesCsvTxtAdapter extends Adapter 
{
	/**
	 * How many lines to parse per command.
	 */
	const PARSE_LINES = 1000;

	/**
	 * If any line length exceeds 1KB, we should parse only 100 lines per command.
	 */
	const PARSE_BIG_LINES = 100;

	/**
	 * Line length that meets this constant will stop parsing and exit with an error
	 */
	const MAX_LINE_LENGTH = 1048577; // 100KB + 1Byte

	protected $_di;

	public function __construct() 
	{
		$this->_di = \Zend_Registry::get("di");
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function export($data) 
	{
		$response = array(
			'results' => array(),
			'commands' => array()
		);

		$params = (array) $data['attributes'];

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

		$title = $local
				->select()
				->from($params['adapter'] . '_adapter', 'title')
				->where('`key` = ?', $params['identity']);

		$title = reset($local->fetchCol($title));

		$columns = $local
				->select()
				->from($params['adapter'] . '_adapter_meta', 'column')
				->where('`key` = ?', $params['identity']);

		$columns = $local->fetchCol($columns);
		$iterations = ceil(( $count / 100));
		$file = APPLICATION_PATH . '/temp/' . Uuid::generateNewUuid();
		$name = implode('_', array('export', $title, date('Y-m-d_Hi'))) . '.' . $params['file_ext'];
		$handle = fopen($file, 'w');

		if (!$handle) {

			return array('errors' => array('apiErrors.filescsvtxt.unableToWrite'));
		}

		fputcsv($handle, $columns);

		for ($i = 1; $i <= $iterations; $i++) {

			try {

				$localResults = $local
						->select()
						->from($params['adapter'] . '_adapter_data')
						->where("`key` = ?", $params['identity'])
						->limitPage($i, 100);

				$localResults = $local->fetchAll($localResults);
			} catch (\Exception $ex) {

				@unlink($file);
				return array('error' => $ex->getMessage());
			}

			foreach ($localResults as $resultRow) {

				$rowVO = unserialize($resultRow['data']);

				if ($rowVO === false && $resultRow['data'] != 'b:0;') {

					@unlink($file);
					return array('errors' => array('apiErrors.filescsvtxt.unableToUnserialize'));
					echo 'Unable to unserialize:' . $resultRow['data'] . " ID:" . $resultRow['identity'];
				}

				fputcsv($handle, (array) $rowVO->attributes->value);
			}
		}

		fclose($handle);

		try {

			$response['results'] = $this->_di->downloadManager->registerFile($file, $name, $data['user_id']);
		} catch (\InvalidArgumentException $ex) {

			@unlink($file);
			switch ($ex->getMessage()) {

				case 'fileNotFound':

					return array('errors' => array('apiErrors.filescsvtxt.registerFileNotFound'));

					break;

				default:

					return array('errors' => array('apiErrors.unknownError'));
			}
		} catch (\Exception $ex) {

			return array('errors' => array('apiErrors.filescsvtxt.registerUnableToReadWriteFile'));
		}

		return $response;
	}

	/**
	 * Tries to parse a csv file. If file has more than 1k rows, reads only first thousand and creates
	 * additional commands to parse the rest.
	 * 
	 * @param array $data
	 * @return array
	 */
	public function parseFile(&$data) 
	{
		$response = array(
			'results' => array(),
			'commands' => array()
		);

		$params = (array) $data['attributes'];

		$parse_lines = isset($params['parse_lines']) ? $params['parse_lines'] : self::PARSE_LINES;

		$handle = @fopen($params['path'], 'r');

		if ($handle === false) {

			return array('errors' => array('apiErrors.filescsvtxt.invalidFilePath'));
		}

		if (!isset($params['from'])) {

			// total amount of lines the file has. empty lines will be skipped
			$total = 0;

			// if line is longer than 1kb there might be problems, cause 1000 lines ~= 1MB of memory load
			while (( $line = fgets($handle, self::MAX_LINE_LENGTH) ) !== false) {

				if (strlen($line) > 1024) {

					$parse_lines = self::PARSE_BIG_LINES;
				}

				if (strlen($line) == self::MAX_LINE_LENGTH) {

					return array('errors' => array('apiErrors.filescsvtxt.lineLengthExceeded'));
				}

				if (!empty($line)) {

					$total++;
				}
			}

			// since you can't rewind in a stream context you have to work around it
			// rewind( $handle );

			fclose($handle);
			$handle = fopen($params['path'], 'r');

			/**
			 * if false - skip command queuing, just parse the damn file!
			 */
			if ($total > $parse_lines) {

				$commandsCount = ceil($total / $parse_lines) - 1;
				$start_from = $parse_lines; // we'll start from 1k, cuz we'll do the first k with this command.
				$command_params = $params;
				$command_params['parse_lines'] = $parse_lines;
				$command_params['parent_identity'] = $data['identity'];

				for ($i = 1; $i <= $commandsCount; $i++) {

					$command_params['from'] = $start_from * $i;
					$command_params['saveresult'] = ( ( $i == $commandsCount ) ? 1 : 0 );

					$commandData = array(
						"priority" => 100,
						"adapter" => "filescsvtxt",
						"title" => $data['title'],
						"user_id" => $data['user_id'],
						"attributes" => $command_params
					);

					$response['commands'][] = $commandData;
				}
			}

			$params['from'] = 0;
		}

		// no additional commands were queued, so we'll just save the result and move on!
		if (empty($params['from']) && empty($response['commands'])) {

			$data['attributes']->saveresult = 1;
		}

		$headers = str_getcsv(strtolower(fgets($handle)), $params['delimiter']);

		$columns = array_map('strtolower', array_keys($params['columns']));

		if (isset($params['from'])) {

			$read = 0;
			$currentLine = 0;

			while (( $line = fgets($handle) ) !== false) {

				$currentLine++;

				// since you can't seek in a stream context, you have to work around it. AND, empty lines are skipped just like in total count above
				if ($currentLine <= $params['from'] || trim($line) == "" || $line == NULL) {

					continue;
				}

				if ($read >= $parse_lines) {

					break;
				}

				$csv = str_getcsv($line, $params['delimiter']);

				$importData = array();

				foreach ($csv as $index => $value) {

					if (!isset($headers[$index])) {

						return array('errors' => array('apiErrors.filescsvtxt.invalidColumnMap'));
					}

					if (in_array(strtolower($headers[$index]), $columns, true)) {

						$importData[$headers[$index]] = mb_convert_encoding($value, "UTF-8");
					}
				}

				if (!empty($importData)) {

					$response['results'][] = $importData;
				}
				$read++;
			}
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
		$message = $this->_di->filescsvtxtQueue->deque();

		if (empty($message)) {
			return;
		}

		// start time. Need to look how long it took
		$timeStart = microtime(true);

		// calling method and taking a look how long it took
		$time1 = microtime(true);

		try {
			switch ($message['class_name']) {

				case 'Domain\Adapter\FilesCsvTxt\Command\ParseFile':

					$result = $this->parseFile($message);
					break;

				case 'Domain\Adapter\FilesCsvTxt\Command\Export':

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
			$this->_di->filescsvtxtQueue->removeFromQueue($message["identity"]);
		} else {
			$this->_di->filescsvtxtQueue->updateFailedQueue($message['identity']);
			// @TODO: jei fail'ina ne pirma karta, increase'int failed
		}

		// need to count total time
		$timeEnd = microtime(true);
		$totalTime = $timeEnd - $timeStart;

		// store all proceed log to system monitor
		$log = array(
			"adapter" => "filescsvtxt",
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
	 * @return \Domain\Adapter\FilesCsvTxt\successEvent 
	 */
	protected function _buildEvent($message, array $result, &$success) 
	{
		switch ($message['class_name']) {

			case 'Domain\Adapter\FilesCsvTxt\Command\ParseFile':

				$successEvent = 'Domain\Adapter\FilesCsvTxt\Event\FileParsed';
				$failedEvent = 'Domain\Adapter\FilesCsvTxt\Event\FileParseFailed';
				$resultVO = 'Domain\Adapter\FilesCsvTxt\ValueObject\RowsList';

				break;

			case 'Domain\Adapter\FilesCsvTxt\Command\Export':

				$successEvent = 'Domain\Adapter\FilesCsvTxt\Event\Exported';
				$failedEvent = 'Domain\Adapter\FilesCsvTxt\Event\ExportFailed';
				$resultVO = 'Domain\Adapter\FilesCsvTxt\ValueObject\ExportedFile';

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