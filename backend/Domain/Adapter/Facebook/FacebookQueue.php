<?php

namespace Domain\Adapter\Facebook;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class FacebookQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_facebook_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "facebook") {
			return;
		}

		parent::enque($command);
	}

}