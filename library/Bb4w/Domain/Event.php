<?php

namespace Bb4w\Domain;

use Bb4w\ValueObject\Uuid;

/**
 * Base class for all events 
 * 
 * @package Kompro
 */
abstract class Event 
{

	/**
	 * @var Uuid Event identity
	 */
	private $_identity;

	/**
	 * @param Uuid $identity 
	 */
	function __construct(Uuid $identity = null) 
	{
		if (is_null($identity)) {
			$this->_identity = Uuid::generateNewUuid();
		} else {
			$this->_identity = $identity;
		}
	}

	/**
	 * Returns event identity
	 * 
	 * @return Uuid Event identity 
	 */
	public function getIdentity() 
	{
		if (is_null($this->_identity)) {
			throw new \Exception("Event has invalid Identity");
		}

		return $this->_identity;
	}

	/**
	 * @return String Event name with namespaces
	 */
	public function getClassName() 
	{
		$className = get_class($this);
		return $className;
	}

}