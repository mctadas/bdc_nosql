<?php

namespace Domain\Adapter\GooglePlus\Event;

use Bb4w\ValueObject\Attributes;
use Bb4w\ValueObject\ErrorCode;
use Bb4w\Domain\ErrorEvent;

/**
 * Error on comments retrievation 
 * 
 * @package Kompro
 */
class CommentsRetrievationFailed extends ErrorEvent 
{

	/**
	 * @var Attributes
	 */
	public $message;

	/**
	 * @param Attributes $message
	 * @param ErrorCode $errorCode 
	 */
	public function __construct(Attributes $message, ErrorCode $errorCode) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($message->value->identity));

		$this->message = $message;
		$this->errorCode = $errorCode;
	}

}