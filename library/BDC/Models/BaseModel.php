<?php

namespace BDC\Models;

// Zend
use \Zend_Db_Adapter_Abstract;
use \Shanty_Mongo_Document;

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
	public function __construct(\Shanty_Mongo_Document $db) 
	{
		$this->_db = $db;
	}

}
