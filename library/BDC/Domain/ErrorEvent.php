<?php

namespace BDC\Domain;

use BDC\ValueObject\ErrorCode;

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