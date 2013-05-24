<?php

namespace ViewModel\Service;

use \MongoId;
use BDC\Models\BaseReadModel;

class Service extends BaseReadModel
{
    protected static $_collection = 'services';
    
    public function createService(array $service)
    {
        $db = $this->get_connection();
        $coll = 'services';
        
        $db->$coll->insert($service);                   
    }
    
    public function getServices($type=null, $id=null)
    {
        $db = $this->get_connection();
        $coll = 'services';
        
        $query = array();
        if(isset($type)){ $query['type'] = $type;};
        if(isset($id)){ $query['_id'] = new MongoId($id);};
        
        return $db->$coll->find($query); 
    }
    
    public function getRandomService()
    {
        $db = $this->get_connection();
        $coll = 'services';
        return $db->$coll->findOne();
    }

    public function removeServices()
    {
    	$db = $this->get_connection();
    	$coll = 'services';
    	return $db->$coll->remove();
    }
    
}
