<?php

namespace Bb4w\ValueObject;

use InvalidArgumentException;

/**
 * @package Kompro 
 */
class Priority extends OptionalInteger 
{
	public function __construct($value) 
	{
		parent::__construct($value);

		if (strlen($this->value) == 0) {
			throw new InvalidArgumentException('emptyString');
		}
	}

}