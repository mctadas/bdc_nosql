<?php

namespace BDC\ValueObject;

use InvalidArgumentException;

/**
 * @package Kompro 
 */
class OptionalInteger 
{

	/**
	 * @var int
	 */
	public $value;

	public function __construct($value) 
	{
		$filteredValue = self::processValue($value);

		$this->value = $filteredValue;
	}

	static protected function processValue($value) 
	{
		// Convert value to integer
		if (is_null($value) || (trim($value) === '')) {
			$filteredValue = null;
		} elseif (is_int($value)) {
			$filteredValue = $value;
		} elseif (is_string($value)) {
			// Value is string, need to cast
			$value = trim($value);

			if (ctype_digit($value)) {
				$filteredValue = (int) $value;
			} else {
				throw new InvalidArgumentException('stringIsNotInteger');
			}
		} else {
			throw new InvalidArgumentException('unsupportedValueType');
		}

		// Save value
		return $filteredValue;
	}

}