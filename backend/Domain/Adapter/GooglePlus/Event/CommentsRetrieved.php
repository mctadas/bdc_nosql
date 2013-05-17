<?php

namespace Domain\Adapter\GooglePlus\Event;

use Bb4w\ValueObject\Attributes;
use Domain\Adapter\GooglePlus\ValueObject\CommentsList;
use Bb4w\Domain\Event;

/**
 * GooglePlus comments retrieved
 * 
 * @package Kompro
 */
class CommentsRetrieved extends Event 
{

	/**
	 * @var CommentsList
	 */
	public $commentsList;

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * @param CommentsList $commentsList
	 * @param Attributes $attributes 
	 */
	public function __construct(
	CommentsList $commentsList, Attributes $attributes) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($attributes->value->identity));

		$this->commentsList = $commentsList;
		$this->attributes = $attributes;
	}

}