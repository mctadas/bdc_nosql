<?php

namespace ViewModel\User;

// Lib
use BDC\Models\BaseReadModel;

class User extends BaseReadModel
{
    protected static $_collection = 'testnames';
    
//     /**
//      * @return array Array of examples.
//      */
//     public function getUsersList()
//     {
//         $select = $this->_db
//                 ->select()
//                 ->from(self::COLLECTION);
        
//         return $this->_db->fetchAll($select);
//     }
    

}
