<?php

namespace Domain\Adapter\MySQL;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class MySQLQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_mysql_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "mysql") {
			return;
		}

		parent::enque($command);
	}

}