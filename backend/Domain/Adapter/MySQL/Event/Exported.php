<?php

namespace Domain\Adapter\MySQL\Event;

use Bb4w\ValueObject\Attributes;
use Domain\Adapter\MySQL\ValueObject\RowsList;
use Bb4w\Domain\Event;

/**
 * DB Exported
 * 
 * @package Kompro
 */
class Exported extends Event 
{

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * @param RowsList $rowsList
	 * @param Attributes $attributes 
	 */
	public function __construct(
	Attributes $attributes) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($attributes->value->identity));

		$this->attributes = $attributes;
	}

}