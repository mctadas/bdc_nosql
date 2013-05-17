<?php

namespace Bb4w\Domain;

/**
 * Base class for all events 
 * 
 * @package Kompro
 */
class EventEnvelope 
{

	/**
	 * @var string
	 */
	protected $_time;

	/**
	 * @var Event
	 */
	public $_event;

	/**
	 * @var array
	 */
	protected $_userData;

	/**
	 * @param string $time
	 * @param Event $event
	 * @param array $userData 
	 */
	public function __construct($time, Event $event, array $userData) 
	{
		$this->_time = $time;
		$this->_event = $event;
		$this->_userData = $userData;
	}

	/**
	 * Time when event occured
	 * 
	 * @return string
	 */
	public function getTime() 
	{
		return $this->_time;
	}

	/**
	 * Event object
	 * 
	 * @return Event
	 */
	public function getEvent() 
	{
		return $this->_event;
	}

	/**
	 * users data
	 * 
	 * @return array
	 */
	public function getUserData() 
	{
		return $this->_userData;
	}

}