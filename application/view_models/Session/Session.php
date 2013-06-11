<?php

namespace ViewModel\Session;

use BDC\Models\BaseReadModel;

class Session extends BaseReadModel
{
    protected static $_collection = 'sessions';
    
    public function save_session($user ,$session_id)
    {
        $db = $this->get_connection();
        $coll = 'sessions';
        $db->$coll->insert(array( 'session'  => $session_id,
                                  'username' => $user['username'],
                                  'category' => $user['category'],
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
