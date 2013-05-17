<?php

namespace Domain\Adapter\Soap;

use Bb4w\Domain\Command;
use Bb4w\Domain\AdapterQueue;

/**
 * @package Kompro 
 */
class SoapQueue extends AdapterQueue 
{

	protected $_tableName = "adapter_soap_queue";

	/**
	 * Adds command to internal queue
	 * 
	 * @param Command $command
	 * @return boolean 
	 */
	public function enque(Command $command) 
	{
		if ($command->adapter->value != "soap") {
			return;
		}

		parent::enque($command);
	}

}