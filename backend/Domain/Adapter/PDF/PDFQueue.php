<?php

namespace Domain\Adapter\PDF;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class PDFQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_pdf_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "pdf") {
			return;
		}

		parent::enque($command);
	}

}