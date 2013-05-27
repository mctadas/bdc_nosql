<?php

namespace BDC\ValueObject;

use InvalidArgumentException;

/**
 * @package Kompro 
 */
class Attributes 
{

	public $value;

	public function __construct(array $value) 
	{
		if (!empty($value)) {

			$vo = new \stdClass();
			foreach ($value as $k => $v) {
				$vo->{$k} = $v;
			}

			$this->value = $vo;
		} else {
			throw new InvalidArgumentException('invalidValue');
		}
	}

}