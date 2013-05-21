<?php

namespace ViewModel\User;

// Lib
use \MongoClient;
use BDC\Models\BaseReadModel;

class User extends BaseReadModel
{
    protected static $_collection = 'users';
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
    
    public function create_user($username, $password, $key, $type='DUMMY')
    {
        // FIXME: probably should go to constructor
        $db = $this->get_connection();
        $coll = 'users'; //$this->_collection;
     
        //actual create querry   
        $db->$coll->insert(array( 'username' => $username,
                                  'password' => $password,
                                  'key'  => $key,
                                  'type' => $type));        
    }
    
    public function get_user($username, $password)
    {
        // FIXME: probably should go to constructor
        $db = $this->get_connection();
        $coll = 'users'; //$this->_collection;
        
        return $db->$coll->findOne(array( 'username' => $username,
                                          'password' => $password));    
    }
    
    public function is_valid($username, $password)
    {
        $user = $this->get_user($username, $password);
        return (isset($user) ? true : false );
    }
    
    //FIXME move elsewhere to another class
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
    
    // Not yet working properly
    public function save()
    {
        $this->_db->name = "test";
        $this->_db->save();
    }
    

}
