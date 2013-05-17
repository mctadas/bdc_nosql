<?php

namespace Bb4w\ValueObject;

use InvalidArgumentException;

/**
 * @package Kompro 
 */
class Adapter 
{

	public $value;

	const TWITTER = 'twitter';
	const FACEBOOK = 'facebook';
	const GOOGLEPLUS = 'googleplus';
	const MYSQL = 'mysql';
	const POSTGRESQL = 'postgresql';
	const ORACLE = 'oracle';
	const FILESCSVTXT = 'filescsvtxt';
	const FILESXLSX = 'filesxlsx';
	const SOAP = 'soap';
	const PDF = 'pdf';
	const NORMALIZED = 'normalized';

	public function __construct($value) 
	{
		$validValues = array(
			self::TWITTER => 1,
			self::FACEBOOK => 1,
			self::GOOGLEPLUS => 1,
			self::MYSQL => 1,
			self::POSTGRESQL => 1,
			self::ORACLE => 1,
			self::FILESCSVTXT => 1,
			self::FILESXLSX => 1,
			self::SOAP => 1,
			self::PDF => 1,
			self::NORMALIZED => 1
		);

		if (isset($validValues[$value])) {
			$this->value = $value;
		} else {
			throw new InvalidArgumentException('invalidValue');
		}
	}

}