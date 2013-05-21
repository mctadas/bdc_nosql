<?php

namespace ViewModel\Session;

// Lib
use \MongoClient;
use BDC\Models\BaseReadModel;

class Session extends BaseReadModel
{
    protected static $_collection = 'sessions';
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
    
    public function save_session($username ,$session_id)
    {
        $db = $this->get_connection();
        $coll = 'sessions';
        $db->$coll->insert(array( 'session'  => $session_id,
                                  'username' => $username,
                                  'time'     => time()));
                                  
    }
    
    public function get_session($session_id){
        $db = $this->get_connection();
        $coll = 'sessions';
        
        return $db->$coll->findOne(array( 'session'  => $session_id)); 
    }
    
    public function remove_session($session_id){
        $db = $this->get_connection();
        $coll = 'sessions';
        
        $db->$coll->remove(array( 'session'  => $session_id)); 
    }
    

}
