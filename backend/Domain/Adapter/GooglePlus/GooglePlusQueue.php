<?php

namespace Domain\Adapter\GooglePlus;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class GooglePlusQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_googleplus_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "googleplus") {
			return;
		}

		parent::enque($command);
	}

}