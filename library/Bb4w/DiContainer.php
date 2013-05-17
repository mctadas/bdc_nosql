<?php

namespace Bb4w;

/**
 * DI Container.
 *
 * Code inpired by http://twittee.org/
 *
 * All services are static by default (you get same instance on each get).
 * 
 * @package Kompro
 */
class DiContainer 
{

	/**
	 * @var array Array of service factories.
	 */
	protected $_services = array();

	/**
	 * @var array Array of static instances.
	 */
	protected $_instances = array();

	public function __get($key) 
	{
		if (array_key_exists($key, $this->_instances)) {
			// This service is static, check if it's already instantiated.
			if (is_null($this->_instances[$key])) {
				// Create and store new service instence.
				$this->_instances[$key] = $this->_services[$key]($this);
				return $this->_instances[$key];
			} else {
				// Return stored service instance.
				return $this->_instances[$key];
			}
		} else {
			// This is instance service, return new instance.
			return $this->_services[$key]($this);
		}
	}

	public function __set($key, $value) 
	{
		$this->_instances[$key] = null;
		$this->_services[$key] = $value;
	}

	/**
	 * Every call to get this specified service will return new instance.
	 *
	 * @param string $key
	 */
	public function markAsInstanceService($key) 
	{
		unset($this->_instances[$key]);
	}

	/**
	 * Get DI containers registered services keys
	 *
	 * @return array Array of keys
	 */
	public function getServicesKeys() 
	{
		return array_keys($this->_services);
	}

}
