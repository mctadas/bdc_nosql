<?php

namespace Bb4w\EventSourcing;

use Bb4w\Domain\Event;

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