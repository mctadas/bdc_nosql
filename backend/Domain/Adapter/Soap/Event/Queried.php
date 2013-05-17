<?php

namespace Domain\Adapter\Soap\Event;

use Bb4w\ValueObject\Attributes;
use Domain\Adapter\Soap\ValueObject\Response;
use Bb4w\Domain\Event;

/**
 * SOAP Queried
 * 
 * @package Kompro
 */
class Queried extends Event 
{

	/**
	 * @var Response
	 */
	public $response;

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * @param Response $response
	 * @param Attributes $attributes 
	 */
	public function __construct(
	Response $response, Attributes $attributes) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($attributes->value->identity));

		$this->response = $response;
		$this->attributes = $attributes;
	}

}