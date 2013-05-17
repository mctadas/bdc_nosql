<?php

namespace BDC\Domain;

/**
 * Queue of commands to be waited to execute 
 * 
 * @package Kompro
 */
class CommandQueue 
{

	/**
	 * @var array
	 */
	private $_queue = array();

	/**
	 * @param Command $command 
	 */
	public function enque(Command $command) 
	{
		array_push($this->_queue, $command);
	}

	/**
	 * @return Command
	 */
	public function deque() 
	{
		return array_shift($this->_queue);
	}

	/**
	 * @return bool
	 */
	public function hasCommands() 
	{
		return !empty($this->_queue);
	}

}