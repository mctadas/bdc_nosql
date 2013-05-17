<?php

namespace Bb4w\Domain;

use Bb4w\ValueObject\ErrorCode;

/**
 * Base class for all error events 
 * 
 * @package Kompro
 */
abstract class ErrorEvent extends Event 
{

	/**
	 * @var ErrorCode klaidos kodas
	 */
	public $errorCode;

}