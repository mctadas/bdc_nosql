<?php

namespace Domain\Adapter\PDF\Event;

use Bb4w\ValueObject\Attributes;
use Domain\Adapter\PDF\ValueObject\ExportedFile;
use Bb4w\Domain\Event;

/**
 * DB Exported
 * 
 * @package Kompro
 */
class Exported extends Event 
{

	/**
	 * @var ExportedFile
	 */
	public $exportedFile;

	/**
	 * @var Attributes
	 */
	public $attributes;

	/**
	 * ExportedFile $exportedFile
	 * @param Attributes $attributes 
	 */
	public function __construct(
	ExportedFile $exportedFile, Attributes $attributes) 
	{
		parent::__construct(new \Bb4w\ValueObject\Uuid($attributes->value->identity));

		$this->exportedFile = $exportedFile;
		$this->attributes = $attributes;
	}

}