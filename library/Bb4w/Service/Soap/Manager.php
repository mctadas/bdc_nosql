<?php

namespace Bb4w\Service\Soap;

class Manager {
    
    public function __construct() {
        
        $this->_db = \Zend_Registry::get( 'db' );
    }
    public function getSystemLog()
    {
        $stmt = $this->_db->query( 'SELECT * FROM system_event_log LIMIT 0,100' );
        
        return $stmt->fetchAll();
    }
    
    public function getMySQLQueue()
    {
        $stmt = $this->_db->query( 'SELECT * FROM adapter_mysql_queue' );
        
        return $stmt->fetchAll();
    }
    
    public function receiveImport( $data )
    {
        file_put_contents( APPLICATION_PATH . '/temp/importdata.txt', $data );
        return '1';
    }
}