<?php

namespace Domain\Adapter\PostgreSQL;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class PostgreSQLQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_postgresql_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "postgresql") {
			return;
		}

		parent::enque($command);
	}

}