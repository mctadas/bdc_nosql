<?php

namespace BDC\ValueObject;

use InvalidArgumentException;

/**
 * Default VO for error codes. Error code must be string
 * 
 * @package Kompro
 */
class ErrorCode 
{

	/**
	 * @var String
	 */
	public $value;

	/**
	 * @param String $value
	 * @throws InvalidArgumentException 
	 */
	public function __construct($value) 
	{
		if (!is_string($value)) {
			throw new InvalidArgumentException('notString');
		}

		$this->value = $value;
	}

}