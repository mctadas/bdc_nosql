<?php

namespace Domain\Adapter\GooglePlus\Event;

use Bb4w\ValueObject\Attributes;
use Domain\Adapter\GooglePlus\ValueObject\UsersList;
use Bb4w\Domain\Event;

/**
 * GooglePlus users found 
 * 
 * @package Kompro
 */
class UsersFound extends Event 
{

	/**
	 * @var UsersList
	 */
	public $usersList;

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * @param UsersList $usersList
	 * @param Attributes $attributes 
	 */
	public function __construct(
	UsersList $usersList, Attributes $attributes) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($attributes->value->identity));

		$this->usersList = $usersList;
		$this->attributes = $attributes;
	}

}