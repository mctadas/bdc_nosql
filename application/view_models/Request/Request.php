<?php

namespace ViewModel\Request;

use \MongoId;
use BDC\Models\BaseWriteModel;

class Request extends BaseWriteModel
{
    public function save(array $request)
    {
        $db = $this->get_connection();
        $coll = 'requests';
        
        $request['time'] = time();
        $db->$coll->insert($request);                      
    }
    
    public function getRequests()
    {
        $db = $this->get_connection();
        $coll = 'requests';
        
        return $db->$coll->find();
    }
        
}
