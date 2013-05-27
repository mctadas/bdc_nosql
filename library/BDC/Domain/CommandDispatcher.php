<?php

namespace BDC\Domain;

use BDC\Domain\CommandQueue;
use BDC\EventSourcing\EventStore;
use BDC\EventSourcing\EventPublisher;

/**
 * Command dispatcher. Executes commands; 
 * 
 * @package Kompro
 */
class CommandDispatcher 
{

	/**
	 * @var CommandQueue
	 */
	private $_commandQueue;

	/**
	 * @var EventStore
	 */
	private $_eventStore;

	/**
	 * @var EventPublisher
	 */
	private $_eventPublisher;

	/**
	 * @var array
	 */
	private $_adapterQueues = array();

	/**
	 * @var bool Used to prevent recursive dispatch calls;
	 */
	private $_dispatchIsRunning = false;

	/**
	 * @param CommandQueue $commandQueue
	 * @param EventStore $eventStore
	 * @param EventPublisher $eventPublisher
	 * @param Array $adapterQueues 
	 */
	public function __construct(
	CommandQueue $commandQueue, EventStore $eventStore, EventPublisher $eventPublisher, array $adapterQueues) 
	{
		$this->_commandQueue = $commandQueue;
		$this->_eventStore = $eventStore;
		$this->_eventPublisher = $eventPublisher;
		$this->_adapterQueues = $adapterQueues;
	}

	/**
	 * This is shortcud. Adds command to queue and starts dispatching
	 * 
	 * @param Command $command 
	 */
	public function executeCommand(Command $command) 
	{
		$this->_commandQueue->enque($command);
		$this->dispatch();
	}

	/**
	 * Runs dispatch loop. Handles commands etc 
	 */
	public function dispatch() 
	{
		// lock
		if ($this->_dispatchIsRunning) {
			return;
		}

		// mark as running
		$this->_dispatchIsRunning = true;

		// loooppopopopo
		while ($this->_commandQueue->hasCommands()) {
			$command = $this->_commandQueue->deque();

			$this->_handleCommand($command);
		}

		// unlock
		$this->_dispatchIsRunning = false;
	}

	/**
	 * @param Command $command 
	 */
	private function _handleCommand(Command $command) 
	{
		// send commands to all queues
		foreach ($this->_adapterQueues as $queue) {
			$queue->enque($command);
		}
	}

	/**
	 * @param UnitOfWork $unitOfWork 
	 */
	public function saveUnitOfWork(UnitOfWork $unitOfWork) 
	{
		$currentUserData = array(); // @TODO this must be userdata
		// save events
		foreach ($unitOfWork->getEvents() as $event) {
			$eventEnvelope = new EventEnvelope(microtime(true), $event, $currentUserData);
			$this->_eventStore->saveEvent($eventEnvelope);
		}

		// add commands to queue
		foreach ($unitOfWork->getCommands() as $command) {
			$this->executeCommand($command);
		}
	}

	/**
	 * Publishes event to listeners
	 * 
	 * @param Event $event 
	 */
	public function publishEvent(Event $event) 
	{
		$this->_eventPublisher->publishEvent($event);
	}

}