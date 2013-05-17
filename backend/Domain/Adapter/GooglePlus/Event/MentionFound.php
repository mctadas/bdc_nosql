<?php

namespace Domain\Adapter\GooglePlus\Event;

use Bb4w\ValueObject\Attributes;
use Domain\Adapter\GooglePlus\ValueObject\MentionsList;
use Bb4w\Domain\Event;

/**
 * GooglePlus mention found 
 * 
 * @package Kompro
 */
class MentionFound extends Event 
{

	/**
	 * @var MentionsList
	 */
	public $mentionsList;

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * @param MentionsList $mentionsList
	 * @param Attributes $attributes 
	 */
	public function __construct(
	MentionsList $mentionsList, Attributes $attributes) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($attributes->value->identity));

		$this->mentionsList = $mentionsList;
		$this->attributes = $attributes;
	}

}