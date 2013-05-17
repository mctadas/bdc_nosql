<?php

namespace Domain\Adapter\FilesCsvTxt\ValueObject;

use Bb4w\ValueObject\Attributes;

/**
 * @package Kompro 
 */
class ExportedFile 
{

	/**
	 * @var string
	 */
	public $attributes;

	/**
	 * @param Attributes $attributes
	 */
	public function __construct(Attributes $attributes) 
	{
		$this->attributes = $attributes;
	}

	/**
	 * @param Attributes $data
	 * @return \Domain\Adapter\FilesCsvTxt\ValueObject\self 
	 */
	static public function buildFromRequestData(Attributes $data) 
	{
		return new self($data);
	}

}