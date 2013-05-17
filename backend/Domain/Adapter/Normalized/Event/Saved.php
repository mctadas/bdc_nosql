<?php

namespace Domain\Adapter\Normalized\Event;

use Bb4w\ValueObject\Attributes;
use Domain\Adapter\Normalized\ValueObject\RowsList;
use Bb4w\Domain\Event;

/**
 * Saved
 * 
 * @package Kompro
 */
class Saved extends Event 
{

	/**
	 * @var RowsList
	 */
	public $rows;

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * @param RowsList $rows
	 * @param Attributes $attributes 
	 */
	public function __construct(
	RowsList $rows, Attributes $attributes) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($attributes->value->identity));

		$this->rows = $rows;
		$this->attributes = $attributes;
	}

}