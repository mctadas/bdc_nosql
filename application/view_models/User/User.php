<?php

namespace ViewModel\User;

use BDC\Models\BaseReadModel;

class User extends BaseReadModel
{
    protected static $_collection = 'users';
    
    public function create_user($username, $password, $key, $type='DUMMY')
    {
        // FIXME: probably should go to constructor
        $db = $this->get_connection();
        $coll = 'users'; //$this->_collection;
     
        //actual create querry   
        $db->$coll->insert(array( 'username' => $username,
                                  'password' => $password,
                                  'key'  => $key,
        		                  'services' => array(),
                                  'type' => $type));        
    }
    
    public function getUserSercives($username)
    {
    	$db = $this->get_connection();
    	$coll = 'users'; //$this->_collection;
    	
    	$user = $db->$coll->findOne(array( 'username' => $username));
    	return (isset($user) ? $user['services'] : array());
    }
    
    public function get_user($username)
    {
        // FIXME: probably should go to constructor
        $db = $this->get_connection();
        $coll = 'users'; //$this->_collection;
        
        return $db->$coll->findOne(array( 'username' => $username));    
    }
    
    
    public function is_valid($username, $password)
    {
        $user = $this->get_user($username);
        return (isset($user) and $user['password'] == $password ? true : false );
    }
    
    public function addService($username, $service)
    {
    	$db = $this->get_connection();
    	$coll = 'users'; //$this->_collection;
    	 
    	$push = array ('$push' => array('services' => $service));
    	$db->$coll->update(array( 'username' => $username), $push);
    }
    
    // Not yet working properly
    public function save()
    {
        $this->_db->name = "test";
        $this->_db->save();
    }
    

}
