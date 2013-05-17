<?php

namespace Bb4w\EventSourcing;

use Bb4w\Domain\EventEnvelope;

/**
 * Interface of event store 
 * 
 * @package Kompro
 */
interface EventStore 
{

	/**
	 * Save event to event log
	 * 
	 * @param EventEnvelope $eventEnvelope 
	 */
	public function saveEvent(EventEnvelope $eventEnvelope);
}