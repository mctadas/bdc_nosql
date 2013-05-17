<?php

namespace Domain\Adapter\Twitter\Event;

use Bb4w\ValueObject\Attributes;
use Bb4w\Domain\Event;

/**
 * Twitter message created
 * 
 * @package Kompro
 */
class TweetCreated extends Event 
{

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * @param Attributes $attributes 
	 */
	public function __construct(
	Attributes $attributes) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($attributes->value->identity));

		$this->attributes = $attributes;
	}

}