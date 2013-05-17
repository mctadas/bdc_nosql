<?php

namespace BDC\Domain;

/**
 * Changes made to the system 
 * 
 * @package Kompro
 */
class UnitOfWork {

	/**
	 * @var array new published events
	 */
	private $_events = array();

	/**
	 * @var array new commands
	 */
	private $_commands = array();

	/**
	 * @param Array/Event $events
	 * @param Array/Command $commands 
	 */
	public function __construct($events, $commands) 
	{
		if (is_array($events)) {
			foreach ($events as $event) {
				$this->addEvent($event);
			}
		} elseif (!empty($events)) {
			$this->addEvent($events);
		}

		if (is_array($commands)) {
			foreach ($commands as $command) {
				$this->addCommand($command);
			}
		} elseif (!empty($commands)) {
			$this->addCommand($commands);
		}
	}

	/**
	 * Adds event to events list
	 * 
	 * @param Event $event 
	 */
	public function addEvent(Event $event) 
	{
		$this->_events[] = $event;
	}

	/**
	 *  Returns all events
	 * 
	 * @return array 
	 */
	public function getEvents() 
	{
		return $this->_events;
	}

	/**
	 * Adds command to commands list
	 * 
	 * @param Command $command 
	 */
	public function addCommand(Command $command) 
	{
		$this->_commands[] = $command;
	}

	/**
	 * Returns all commands
	 * 
	 * @return array
	 */
	public function getCommands() 
	{
		return $this->_commands;
	}

}