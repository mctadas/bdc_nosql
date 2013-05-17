<?php

namespace Domain\Adapter\Oracle;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class OracleQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_oracle_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "oracle") {
			return;
		}

		parent::enque($command);
	}

}