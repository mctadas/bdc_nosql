<?php

namespace ViewModel\Example\EventHandler;

use Domain\Adapter\Twitter\Event\MentionFound;

// Lib
use BDC\Models\BaseWriteModel;
use ReadModel\User as UserReadModel;
// Zend
use \Zend_Db_Adapter_Abstract;
use \Zend_Session_Namespace;

class ExampleUpdater extends BaseWriteModel {

    const TABLE_NAME = 'example';

    /**
     * @var ExampleReadModel
     */
    protected $_exampleReadModel;

    /**
     * @param Zend_Db_Adapter_Abstract $db
     */
    public function __construct(
        Zend_Db_Adapter_Abstract $db
    ) {
        parent::__construct($db);
    }

    public function mentionFound(MentionFound $event)
    {
//        return;
        var_dump($event);
        die(__METHOD__);
    }
    
    public function create(array $data) {
        $domainErrors = array();
        
                $insertData = array(
                    'id' => \Bb4w\Uuid::random(),
                    'column' => $data['column'],
                );

        $this->_db->insert(self::TABLE_NAME, $insertData);

        return (empty($domainErrors)) ? true : $domainErrors;
    }


}
