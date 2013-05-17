<?php

namespace BDC\EventSourcing;

use BDC\Domain\Event;

/**
 * Interface of event publisher 
 * 
 * @package Kompro
 */
interface EventPublisher 
{

	/**
	 * Notify external event handlers
	 * 
	 * @param Event $event 
	 */
	public function publishEvent(Event $event);
}