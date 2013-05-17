<?php

namespace Domain\Adapter\FilesCsvTxt\Event;

use Bb4w\ValueObject\Attributes;
use Domain\Adapter\FilesCsvTxt\ValueObject\RowsList;
use Bb4w\Domain\Event;

/**
 * CSV parsed
 * 
 * @package Kompro
 */
class FileParsed extends Event 
{

	/**
	 * @var RowsList
	 */
	public $rowsList;

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * @param RowsList $rowsList
	 * @param Attributes $attributes 
	 */
	public function __construct(
	RowsList $rowsList, Attributes $attributes) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($attributes->value->identity));

		$this->rowsList = $rowsList;
		$this->attributes = $attributes;
	}

}