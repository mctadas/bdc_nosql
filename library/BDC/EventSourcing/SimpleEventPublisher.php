<?php

namespace BDC\EventSourcing;

use BDC\Domain\Event;
use InvalidArgumentException;

/**
 * Simple implementation of event publisher 
 * 
 * @package Kompro
 */
class SimpleEventPublisher implements EventPublisher 
{

	/**
	 * @var array
	 */
	private $_eventHandlers = array();

	/**
	 * Register Event handlers
	 * 
	 * @param array $eventHandlers 
	 */
	public function registerEventHandlers(array $eventHandlers) 
	{
		foreach ($eventHandlers as $eventName => $eventHandlersList) {
			foreach ($eventHandlersList as $eventHandler) {
				if (is_callable($eventHandler)) {
					$this->_eventHandlers[$eventName][] = $eventHandler;
				} else {
					throw new InvalidArgumentException("Event handler is not callable");
				}
			}
		}
	}

	/**
	 * Notify external event handlers
	 * 
	 * @param Event $event 
	 */
	public function publishEvent(Event $event) 
	{
		$eventName = $event->getClassName();

		// check for listeners
		if (isset($this->_eventHandlers[$eventName])) {

			// iterate through all assigned listeners
			foreach ($this->_eventHandlers[$eventName] as $eventHandlerCallback) {
				$eventHandlerCallback($event);
			}
		}
	}

}