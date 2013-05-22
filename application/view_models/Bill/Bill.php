<?php

namespace ViewModel\Bill    ;

// Lib
use \MongoClient;
use BDC\Models\BaseReadModel;

class Bill extends BaseReadModel
{
    protected static $_collection = 'bills';
    protected $_conn;
    
    public function get_connection()
    {
        if(empty($this->_conn))
        {
            $m = new MongoClient();
            $this->_conn = $m->mt;
        }
        return $this->_conn;
             
    }
    
    public function save(array $bill_doc ,$user_key)
    {
        $db = $this->get_connection();
        $coll = 'bills';
        
        $bill_doc['ukey'] =  $user_key ;
        $db->$coll->insert($bill_doc);
                                  
    }
    
    public function get_bills($user_key){
        $db = $this->get_connection();
        $coll = 'bills';
        
        return $db->$coll->find(array( 'ukey'  => $user_key)); 
    }
    

}
