<?php

namespace ViewModel\Example;

// Lib
use BDC\Models\BaseReadModel;

class Example extends BaseReadModel
{
    const TABLE_NAME = 'example';
    
    /**
     * @return array Array of examples.
     */
    public function getExamplesList()
    {
        $select = $this->_db
                ->select()
                ->from(self::TABLE_NAME);
        
        return $this->_db->fetchAll($select);
    }
    

}
