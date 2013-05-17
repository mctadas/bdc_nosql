<?php

namespace Domain\Adapter\FilesXlsx;

use Bb4w\ValueObject\Uuid;
use Bb4w\Domain\Adapter;
use Bb4w\Domain\UnitOfWork;
use Bb4w\ValueObject\ErrorCode;
use Bb4w\ValueObject\Attributes;
use Domain\Adapter\FilesXlsx\Command\ParseFile as ParseFileCommand;

/**
 * filesxlsx adapter class.
 * 
 * @package Kompro
 */
class FilesXlsxAdapter extends Adapter 
{
	/**
	 * How many lines to parse per command.
	 */
	const READ_LINES = 100;

	/**
	 * Where to download the .xlsx file (APPLICATION_PATH . TEMP_DOWNLOAD_DIR . FILE.XLSX)
	 */
	const TEMP_DOWNLOAD_DIR = '/temp/';

	/**
	 * Bytes to read/write at once during download.
	 */
	const TEMP_DOWNLOAD_CHUNKSIZE = 10240; // 10KB

	protected $_di;

	public function __construct() 
	{
		$this->_di = \Zend_Registry::get("di");
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function exportDocx($data) 
	{
		$response = array(
			'commands' => array(),
			'results' => array()
		);

		$params = (array) $data['attributes'];

		$imageData = @file_get_contents($params['imageurl']);

		if (empty($imageData)) {

			return array('error' => 'apiErrors.filesxlsx.invalidImageUrl');
		}

		$image = APPLICATION_PATH . '/temp/' . Uuid::generateNewUuid() . '.' . end(explode('.', $params['imageurl']));
		$file = APPLICATION_PATH . '/temp/' . Uuid::generateNewUuid();
		$name = implode('_', array('export', $data['title'], date('Y-m-d_Hi'))) . '.' . $params['filetype'];

		file_put_contents($image, $imageData);

		try {

			$word = new \PHPWord();

			$section = $word->createSection();
			$section->addImage($image);

			$writer = \PHPWord_IOFactory::createWriter($word, 'Word2007');
			$writer->save($file);
		} catch (\Exception $ex) {

			return array('error' => $ex->getMessage());
		}
		@unlink($image);

		try {
			$response['results'] = $this->_di->downloadManager->registerFile($file, $name, $data['user_id']);
		} catch (\InvalidArgumentException $ex) {

			@unlink($file);
			switch ($ex->getMessage()) {

				case 'fileNotFound':

					return array('errors' => array('apiErrors.filesxlsx.registerFileNotFound'));

					break;

				default:

					return array('errors' => array('apiErrors.unknownError'));
			}
		} catch (\Exception $ex) {

			return array('errors' => array('apiErrors.filesxlsx.registerUnableToReadWriteFile'));
		}
		return $response;
	}

	/**
	 * @param array $data
	 * @return array 
	 */
	public function exportXlsx($data) 
	{
		$response = array(
			'commands' => array(),
			'results' => array()
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

			return array('error' => 'apiErrors.zeroresults');
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
		$name = implode('_', array('export', $title, date('Y-m-d_Hi'))) . '.' . $params['filetype'];

		$cacheMethod = \PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array(' memoryCacheSize ' => '8MB');

		\PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

		$excel = new \PHPExcel();

		$excel->getProperties()->setCreator('Gaumina');
		$excel->getProperties()->setLastModifiedBy('Gaumina');
		$excel->getProperties()->setTitle($title);
		$excel->getProperties()->setSubject($title);
		$excel->getProperties()->setDescription($title);
		$excel->setActiveSheetIndex(0);

		$rowIndex = 1;

		foreach ($columns as $index => $column) {

			$excel->getActiveSheet()->setCellValue($this->getExcelColumn($index) . $rowIndex, $column);
		}

		$rowIndex++;

		for ($i = 1; $i <= $iterations; $i++) {

			try {

				$localResults = $local
						->select()
						->from($params['adapter'] . '_adapter_data')
						->where("`key` = ?", $params['identity'])
						->limitPage($i, 100);

				$localResults = $local->fetchAll($localResults);
			} catch (\Exception $ex) {

				$excel = null;
				return array('error' => $ex->getMessage());
			}

			foreach ($localResults as $resultRow) {

				$rowVO = unserialize($resultRow['data']);
				$colIndex = 0;

				if ($rowVO === false && $resultRow['data'] != 'b:0;') {

					$excel = null;
					return array('error' => 'Unable to unserialize the result:' . $resultRow['data'] . " ID:" . $resultRow['identity']);
				}

				foreach ($rowVO->attributes->value as $val) {

					$column = $this->getExcelColumn($colIndex);

					$excel->getActiveSheet()->setCellValue($column . $rowIndex, $val);

					$colIndex++;
				}

				$rowIndex++;
			}
		}

		$writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$writer->save($file);

		$response['results'] = $this->_di->downloadManager->registerFile($file, $name, $data['user_id']);

		return $response;
	}

	/**
	 * Paduodam int'a, gaunam excelio column'a. 0 - A, 1 - B, 1500000 - CGHXI, etc.
	 * 
	 * @param int $column
	 * @return string
	 */
	protected function getExcelColumn($column) 
	{
		if (!is_numeric($column)) {

			return;
		}

		$div = $column / 26;

		if ($div >= 1) {

			return $this->getExcelColumn($mod - 1) . chr(( $column % 26 ) + ord('A'));
		} else {

			return chr($column + ord('A'));
		}
	}

	/**
	 * Tries to parse a xlsx file. If file has more than 1k rows, reads only first thousand and creates
	 * additional commands to parse the rest.
	 * 
	 * @param array $data
	 * @return array
	 */
	public function parseFile(&$data) 
	{
		$response = array(
			'commands' => array(),
			'results' => array()
		);

		$params = (array) $data['attributes'];

		if (!isset($params['localpath'])) {

			try {

				$params['localpath'] = $this->download($params['path'], self::TEMP_DOWNLOAD_CHUNKSIZE);
			} catch (\InvalidArgumentException $ex) {

				return array('error' => $ex->getMessage());
			}
		}

		try {

			$reader = \PHPExcel_IOFactory::createReaderForFile($params['localpath']);
		} catch (\Exception $ex) {

			@unlink($params['localpath']);
			return array('error' => $ex->getMessage());
		}

		if (empty($params['from'])) {

			$loadedExcel = $reader->load($params['localpath']);

			$total = $loadedExcel->getActiveSheet()->getHighestRow();

			if ($total > self::READ_LINES) {

				$commandsCount = ceil($total / self::READ_LINES) - 1;
				$start_from = self::READ_LINES;
				$command_params = $params;
				$command_params['parent_identity'] = $data['identity'];

				for ($i = 1; $i <= $commandsCount; $i++) {

					$command_params['from'] = $start_from * $i;
					$command_params['saveresult'] = ( ( $i == $commandsCount ) ? 1 : 0 );

					$commandData = array(
						"priority" => 100,
						"adapter" => "filesxlsx",
						"title" => $data['title'],
						"user_id" => $data['user_id'],
						"attributes" => $command_params
					);

					$response['commands'][] = $commandData;
				}
			}

			$params['from'] = 0;
		}

		$reader->setReadFilter(new \Bb4w\PHPExcel\Filters\ChunkReader(( $params['from'] + 1), self::READ_LINES));

		$loadedExcel = $reader->load($params['localpath']);

		$results = $loadedExcel->getActiveSheet()->toArray($nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false);

		foreach ($results as $result) {

			$colIndex = 0;
			$resultRow = array();
			foreach ($params['columns'] as $column => $type) {

				if (isset($result[$colIndex])) {

					$resultRow[$column] = $result[$colIndex];
				}

				$colIndex++;
			}

			if (!empty($resultRow)) {

				$response['results'][] = $resultRow;
			}
		}

		// skipping first row
		if (!empty($params['skip']) && $params['from'] == 0) {

			$headers = array_shift($response['results']);
		}

		// no commands were generated, we can save the result, delete the temp file and move on.
		if (empty($params['from']) && empty($response['commands'])) {

			$data['attributes']->saveresult = 1;
			@unlink($params['localpath']);
		}

		return $response;
	}

	/**
	 * Downloads a file of any size in $chunkSize chunks, returns saved file path.
	 *  
	 * @param string $remote    - remote file path
	 * @param int    $chunkSize - chunk size
	 * @param string $extension - file extension
	 * 
	 * @return string - path to a downloaded local file
	 * @throws \InvalidArgumentException
	 */
	private function download($remote, $chunkSize = 10240, $extension = '.xlsx') 
	{
		$filename = Uuid::generateNewUuid() . $extension;
		$local = APPLICATION_PATH . self::TEMP_DOWNLOAD_DIR . $filename;

		$handleRead = @fopen($remote, 'r');
		$handleWrite = @fopen($local, 'x'); // x - if file exists return false, else create empty

		if (!$handleRead) {

			throw new \InvalidArgumentException('apiErrors.filesxlsx.unableToReadWrite');
		}

		if (!$handleWrite) {

			throw new \InvalidArgumentException('apiErrors.filesxlsx.unableToReadWrite');
		}

		while (!feof($handleRead)) {

			fwrite($handleWrite, fread($handleRead, $chunkSize));
		}

		fclose($handleWrite);
		fclose($handleRead);

		return $local;
	}

	/**
	 * @return \Bb4w\Domain\UnitOfWork 
	 */
	public function proceed() 
	{
		// procesas gali vykti pakankamai ilgai
		set_time_limit(0);

		// taking command (message) from inner queue
		$message = $this->_di->filesxlsxQueue->deque();

		if (empty($message)) {

			return;
		}

		// start time. Need to look how long it took
		$timeStart = microtime(true);

		// calling method and taking a look how long it took
		$time1 = microtime(true);

		try {
			switch ($message['class_name']) {

				case 'Domain\Adapter\FilesXlsx\Command\ParseFile':

					$result = $this->parseFile($message);
					break;

				case 'Domain\Adapter\FilesXlsx\Command\Export':

					if ($message['attributes']->filetype == 'docx') {

						$result = $this->exportDocx($message);
					} else {

						$result = $this->exportXlsx($message);
					}
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
			$this->_di->filesxlsxQueue->removeFromQueue($message["identity"]);
		} else {
			$this->_di->filesxlsxQueue->updateFailedQueue($message['identity']);
			// @TODO: jei fail'ina ne pirma karta, increase'int failed
		}

		// need to count total time
		$timeEnd = microtime(true);
		$totalTime = $timeEnd - $timeStart;

		// store all proceed log to system monitor
		$log = array(
			"adapter" => "filesxlsx",
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
	 * @return \Domain\Adapter\FilesXlsx\successEvent 
	 */
	protected function _buildEvent($message, array $result, &$success) 
	{
		switch ($message['class_name']) {

			case 'Domain\Adapter\FilesXlsx\Command\ParseFile':

				$successEvent = 'Domain\Adapter\FilesXlsx\Event\FileParsed';
				$failedEvent = 'Domain\Adapter\FilesXlsx\Event\FileParseFailed';
				$resultVO = 'Domain\Adapter\FilesXlsx\ValueObject\RowsList';

				break;

			case 'Domain\Adapter\FilesXlsx\Command\Export':

				$successEvent = 'Domain\Adapter\FilesXlsx\Event\Exported';
				$failedEvent = 'Domain\Adapter\FilesXlsx\Event\ExportFailed';
				$resultVO = 'Domain\Adapter\FilesXlsx\ValueObject\ExportedFile';

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