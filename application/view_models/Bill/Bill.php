<?php

namespace ViewModel\Bill    ;

// Lib
use \MongoClient;
use \MongoId;
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
    
    public function get_bills($user_key)
    {
        $db = $this->get_connection();
        $coll = 'bills';
        
        return $db->$coll->find(array( 'ukey'  => $user_key), array ('pdf_doc' => 0, 'pdf_report' => 0 )); 
    }
        
    public function get_bill_document($bill_id, $doc_key)
    {
        $db = $this->get_connection();
        $coll = 'bills';
        
        $doc = $db->$coll->findOne(array( '_id'  => new MongoId($bill_id)), array($doc_key));
        return $doc[$doc_key]->bin;
    }

}
