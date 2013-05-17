<?php

namespace Domain\Adapter\PDF;

use Bb4w\ValueObject\Uuid;
use Bb4w\Domain\Adapter;
use Bb4w\Domain\UnitOfWork;
use Bb4w\ValueObject\ErrorCode;
use Bb4w\ValueObject\Attributes;

/**
 * pdf adapter class.
 * 
 * @package Kompro
 */
class PDFAdapter extends Adapter 
{

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
			'results' => array()
		);

		$params = (array) $data['attributes'];

		$imageData = @file_get_contents($params['imageurl']);

		if (empty($imageData)) {

			return array('error' => 'apiErrors.soap.invalidImageUrl');
		}

		$file = APPLICATION_PATH . '/temp/' . Uuid::generateNewUuid();
		$name = implode('_', array('export', $data['title'], date('Y-m-d_Hi'))) . '.pdf';

		require_once( 'TCPDF/tcpdf.php');

		// create new PDF document
		$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Nicola Asuni');
		$pdf->SetTitle('TCPDF Example 009');
		$pdf->SetSubject('TCPDF Tutorial');
		$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// add a page
		$pdf->AddPage();

		// set JPEG quality
		$pdf->setJPEGQuality(100);

		/*
		 *  The '@' character is used to indicate that follows an image data stream and not an image file name.
		 *  Other parameters RTFM: http://www.tcpdf.org/doc/classTCPDF.html#a714c2bee7d6b39d4d6d304540c761352
		 */
		$pdf->Image('@' . $imageData, '', '', 0, 0, '', '', '', false, 300, '', false, false, 0, false, false, true, false, array());

		$pdf->Output($file, 'F');

		$response['results'] = $this->_di->downloadManager->registerFile($file, $name, $data['user_id']);

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
		$message = $this->_di->pdfQueue->deque();

		if (empty($message)) {

			return;
		}

		// start time. Need to look how long it took
		$timeStart = microtime(true);

		// calling method and taking a look how long it took
		$time1 = microtime(true);

		try {
			switch ($message['class_name']) {

				case 'Domain\Adapter\PDF\Command\Export':

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
			$this->_di->pdfQueue->removeFromQueue($message["identity"]);
		} else {
			$this->_di->pdfQueue->updateFailedQueue($message['identity']);
			// @TODO: jei fail'ina ne pirma karta, increase'int failed
		}

		// need to count total time
		$timeEnd = microtime(true);
		$totalTime = $timeEnd - $timeStart;

		// store all proceed log to system monitor
		$log = array(
			"adapter" => "pdf",
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
	 * @return \Domain\Adapter\PDF\successEvent 
	 */
	protected function _buildEvent($message, array $result, &$success) 
	{
		switch ($message['class_name']) {

			case 'Domain\Adapter\PDF\Command\Export':

				$successEvent = 'Domain\Adapter\PDF\Event\Exported';
				$failedEvent = 'Domain\Adapter\PDF\Event\ExportFailed';
				$resultVO = 'Domain\Adapter\PDF\ValueObject\ExportedFile';

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