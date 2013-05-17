<?php

namespace Domain\Adapter\Soap\ValueObject;

use Bb4w\ValueObject\Uuid;
use Bb4w\ValueObject\Attributes;
use Exception;
use InvalidArgumentException;

/**
 * @package Kompro 
 */
class Item 
{

	/**
	 * @var Uuid
	 */
	public $identity;

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * @param Uuid $identity
	 * @param Attributes $attributes 
	 */
	public function __construct(
	Uuid $identity, Attributes $attributes) 
	{
		$this->identity = $identity;
		$this->attributes = $attributes;
	}

}