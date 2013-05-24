<?php

namespace BDC\Models;

use \Zend_Db_Adapter_Abstract;
use \MongoClient;
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
	
    protected $_conn;

	/**
	 * @param Zend_Db_Adapter_Abstract $db 
	 */
	public function __construct(\Shanty_Mongo_Document $db) 
	{
		$this->_db = $db;
	}
    
	public function get_connection()
    {
        if(empty($this->_conn))
        {
            if(APPLICATION_ENV == 'production')
            {
                $m = new MongoClient('mongodb://10.248.2.24:27017');
            } else {
                $m = new MongoClient('mongodb://localhost:27017');
            }
            $this->_conn = $m->mt;
        }
        return $this->_conn;
             
    }

}
