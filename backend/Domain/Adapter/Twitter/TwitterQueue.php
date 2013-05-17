<?php

namespace Domain\Adapter\Twitter;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class TwitterQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_twitter_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "twitter") {
			return;
		}

		parent::enque($command);
	}

}