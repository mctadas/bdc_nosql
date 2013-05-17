<?php

namespace Bb4w\ValueObject;

// PHP
use InvalidArgumentException;

/**
 * A Universally Unique IDentifier (UUID) implementation. Can also accept null or empty string.
 *
 * See http://www.apps.ietf.org/rfc/rfc4122.html for details.
 * 
 * @package Kompro
 */
class OptionalUuid 
{

	/**
	 * @var string|null UUID value. Lowercase, without curly brackets, with dashes. May be null.
	 */
	public $value;

	/**
	 * Will convert UUID to lowercase and strip curly brackets. Can also accept null or empty string.
	 *
	 * @param string|null $value
	 */
	public function __construct($value) 
	{
		if (empty($value)) {
			$this->value = null;
		} else {
			// Convert to lower
			$filteredValue = strtolower($value);

			// Validate UUID
			$pattern = '/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i';
			if (preg_match($pattern, $filteredValue) !== 1) {
				throw new InvalidArgumentException('invalidUuid');
			}

			// Strip curly brackets
			$filteredValue = str_replace(array('{', '}'), '', $filteredValue);

			// Store value
			$this->value = $filteredValue;
		}
	}

}
