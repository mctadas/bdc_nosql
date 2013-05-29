<?php

namespace ViewModel\Service\EventHandler;

use BDC\Models\BaseReadModel;

class Updater extends BaseWriteModel
{
    protected static $_collection = 'service';
    
    public function createService( $event )
    {
        var_dump($event);
        die(__FILE__);
        // FIXME: probably should go to constructor
        $db = $this->get_connection();
        $coll = 'users'; //$this->_collection;
     
        //actual create querry   
        $db->$coll->insert(array( 'username' => $username,
                                  'password' => $password,
                                  'key'  => $key,
                                  'type' => $type));        
    }
    

}
