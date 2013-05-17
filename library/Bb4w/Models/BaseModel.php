<?php

namespace Bb4w\Models;

// Zend
use \Zend_Db_Adapter_Abstract;

/**
 * @package Kompro 
 */
abstract class BaseModel 
{

	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db;

	/**
	 * @param Zend_Db_Adapter_Abstract $db 
	 */
	public function __construct(Zend_Db_Adapter_Abstract $db) 
	{
		$this->_db = $db;
	}

}
