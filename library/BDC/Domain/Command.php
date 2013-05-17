<?php

namespace BDC\Domain;

/**
 * Base class for all commands 
 * 
 * @package Kompro
 */
abstract class Command 
{

	public $identity;
	public $priority;
	public $adapter;
	public $attributes;

	/**
	 * Build command from requestData
	 * 
	 * @param array $requestData
	 * @throws \Exception 
	 * @return Object of itself || Array validationErrors
	 */
	static public function buildFromRequestData(array $requestData) 
	{
		throw new \Exception("Not implemented");
	}

}