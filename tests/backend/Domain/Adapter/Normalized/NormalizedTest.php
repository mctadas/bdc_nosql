<?php

class NormalizedTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
        
        require_once( APPLICATION_PATH . '/modules/api/handler/NormalizedHandler.php');
        
        $this->bootstrap = new Zend_Application( 'staging', APPLICATION_PATH . '/configs/application.ini');
        
        parent::setUp();
        
        $this->handler = new NormalizedHandler();
    }
    
    public function testSaveSuccess()
    {
        $params = array(
            'user_id'  => 123,
            'table'    => '3675cea1d10140fce4faec9c85363e65',
            'title'    => 'title',
            'priority' => 100,
        );
        
        // Part one: create mock data
        
        $dbNormalizer = \Zend_Registry::get( 'db_normalizer' );
        
        $dbNormalizer->query( 'DROP TABLE IF EXISTS `' . $params['table'] . '`' );
        $dbNormalizer->query( 'CREATE TABLE `' . $params['table'] . '` (
                                `070657c2-88bc-4915-afa9-5901a24bf070_title` varchar(255) DEFAULT NULL,
                                `070657c2-88bc-4915-afa9-5901a24bf070_published` datetime DEFAULT NULL,
                                `070657c2-88bc-4915-afa9-5901a24bf070_actor_displayname` varchar(255) DEFAULT NULL,
                                `3758768d-b200-4d37-bc50-9d71f2f5031c_name` varchar(255) DEFAULT NULL
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        
        for ( $i = 0; $i < 100; $i++ ) {
            
            $dbNormalizer->insert( $params['table'], array(
                '070657c2-88bc-4915-afa9-5901a24bf070_title'             => mt_rand( 0, time() ) . "",
                '070657c2-88bc-4915-afa9-5901a24bf070_published'         => date( 'Y-m-d H:i:s' ),
                '070657c2-88bc-4915-afa9-5901a24bf070_actor_displayname' => 'lol',
                '3758768d-b200-4d37-bc50-9d71f2f5031c_name'              => 'lol',
            ));
        }
        
        // Part two: enque the command
        $response = $this->handler->save( $params['user_id'], $params['table'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part three: deque and execute the command
        $adapter = new Domain\Adapter\Normalized\NormalizedAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Normalized\Event\Saved', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testSaveFailureInvalidTable()
    {
        $params = array(
            'user_id'  => 123,
            'table'    => '3675cea1d10140fce4faec9c8ccccccc',
            'title'    => 'title',
            'priority' => 100,
        );
        
        $response = $this->handler->save( $params['user_id'], $params['table'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        $adapter = new Domain\Adapter\Normalized\NormalizedAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Normalized\Event\SaveFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testSaveFailureMissingTableName()
    {
        $params = array(
            'user_id'  => 123,
            'table'    => '',
            'title'    => 'title',
            'priority' => 100,
        );
        
        $response = $this->handler->save( $params['user_id'], $params['table'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testSaveFailureInvalidTableName()
    {
        $params = array(
            'user_id'  => 123,
            'table'    => '3675cea1d10140fce4faec9ccas85363e65',
            'title'    => 'title',
            'priority' => 100,
        );
        
        $response = $this->handler->save( $params['user_id'], $params['table'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testSaveFailureInvalidUserID()
    {
        $params = array(
            'user_id'  => 'labas',
            'table'    => '3675cea1d10140fce4faec9ccas85363e65',
            'title'    => 'title',
            'priority' => 100,
        );
        
        $response = $this->handler->save( $params['user_id'], $params['table'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function tearDown()
    {
        $db = Zend_Registry::get( 'db' );
        $db->query( 'TRUNCATE TABLE adapter_normalized_queue' );
        $db->query( 'TRUNCATE TABLE normalized_adapter' );
        $db->query( 'TRUNCATE TABLE normalized_adapter_data' );
        $db->query( 'TRUNCATE TABLE normalized_adapter_meta' );
        parent::tearDown();
    }
}