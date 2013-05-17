<?php

namespace Bb4w\ValueObject;

// PHP
use InvalidArgumentException;

/**
 * A Universally Unique IDentifier (UUID) implementation. Don't accept empty values.
 *
 * See http://www.apps.ietf.org/rfc/rfc4122.html for details.
 * 
 * @package Kompro
 */
class Uuid extends OptionalUuid 
{

	/**
	 * @var string UUID value. Lowercase, without curly brackets, with dashes. Can not be empty.
	 */
	public $value;

	/**
	 * Will convert UUID to lowercase and strip curly brackets.
	 *
	 * @param string $value
	 */
	public function __construct($value) 
	{
		// Check if value is not empty
		if (empty($value)) {
			throw new InvalidArgumentException('valueCanNotBeEmpty');
		}

		parent::__construct($value);
	}

	/**
	 * @return string
	 */
	public function __toString() 
	{

		return $this->value;
	}

	/**
	 * Generate a pseudo-random UUID.
	 *
	 * Implementation is taken from http://php.net/manual/en/function.uniqid.php#69164
	 *
	 * @return Uuid
	 */
	public static function generateNewUuid() 
	{
		$newGuid = sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

		return new self($newGuid);
	}

}
