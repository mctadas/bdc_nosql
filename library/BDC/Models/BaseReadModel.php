<?php

namespace BDC\Models;

use \MongoClient;

abstract class BaseReadModel extends BaseModel 
{

    protected $_conn;
    
	public function get_connection()
    {
        if(empty($this->_conn))
        {
            if($_ENV['APPLICATION_ENV'] == 'production')
            {
                $m = new MongoClient('mongodb://10.248.2.24:27017');
            } else {
                $m = new MongoClient();
            }
            $this->_conn = $m->mt;
        }
        return $this->_conn;
             
    }
}
