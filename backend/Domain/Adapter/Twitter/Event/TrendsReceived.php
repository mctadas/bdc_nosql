<?php

namespace Domain\Adapter\Twitter\Event;

use Bb4w\ValueObject\Attributes;
use Domain\Adapter\Twitter\ValueObject\TrendsList;
use Bb4w\Domain\Event;

/**
 * Twitter trends received
 * 
 * @package Kompro
 */
class TrendsReceived extends Event 
{

	/**
	 * @var Trends list
	 */
	public $trendsList;

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * @param TrendsList $trendsList
	 * @param Attributes $attributes 
	 */
	public function __construct(
	TrendsList $trendsList, Attributes $attributes) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($attributes->value->identity));

		$this->trendsList = $trendsList;
		$this->attributes = $attributes;
	}

}