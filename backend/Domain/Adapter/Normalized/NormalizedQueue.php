<?php

namespace Domain\Adapter\Normalized;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class NormalizedQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_normalized_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "normalized") {
			return;
		}

		parent::enque($command);
	}

}