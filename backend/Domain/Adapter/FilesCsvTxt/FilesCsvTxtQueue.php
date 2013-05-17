<?php

namespace Domain\Adapter\FilesCsvTxt;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class FilesCsvTxtQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_filescsvtxt_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "filescsvtxt") {
			return;
		}

		parent::enque($command);
	}

}