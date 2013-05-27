<?php

namespace BDC\EventSourcing;

use BDC\Domain\EventEnvelope;
// Zend
use Zend_Db_Adapter_Abstract;
// PHP
use RuntimeException;

/**
 * Event storage 
 * 
 * @package Kompro
 */
class SimpleEventStore implements EventStore 
{

	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	private $_dbAdapter;

	/**
	 * @var String
	 */
	private $_eventLogTableName;

	/**
	 * @param Zend_Db_Adapter_Abstract $dbAdapter
	 * @param string $eventLogTableName 
	 */
	public function __construct(Zend_Db_Adapter_Abstract $dbAdapter, $eventLogTableName) 
	{
		$this->_dbAdapter = $dbAdapter;
		$this->_eventLogTableName = $eventLogTableName;
	}

	/**
	 * @param EventEnvelope $eventEnvelope 
	 */
	public function saveEvent(EventEnvelope $eventEnvelope) 
	{
		$event = $eventEnvelope->getEvent();

		$this->_dbAdapter->insert(
				$this->_eventLogTableName, array(
			'time' => $eventEnvelope->getTime(),
			'event_identity' => $event->getIdentity()->value,
			'event_name' => $event->getClassName(),
			'serialized_event' => serialize($eventEnvelope),
				)
		);
	}

}