<?php

namespace Domain\Adapter\FilesXlsx;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class FilesXlsxQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_filesxlsx_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "filesxlsx") {
			return;
		}

		parent::enque($command);
	}

}