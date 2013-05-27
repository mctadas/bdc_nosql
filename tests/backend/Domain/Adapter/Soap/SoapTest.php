<?php

class SoapTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
        
        require_once( APPLICATION_PATH . '/modules/api/handler/SoapHandler.php');
        require_once( APPLICATION_PATH . '/modules/api/handler/ExportHandler.php');
        
        $this->bootstrap = new Zend_Application( 'staging', APPLICATION_PATH . '/configs/application.ini');
        
        parent::setUp();
        
        $this->handler = new SoapHandler();
        $this->export  = new ExportHandler();
    }
    
    public function testQuerySuccess()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/server',
            'ws_method'  => 'getSystemLog',
            'columns'    => array( 'seven' => 'varchar' ),
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->query( $params['user_id'], $params['server'], $params['ws_method'], $params['columns'], $params['username'], $params['password'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );

        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Soap\SoapAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Soap\Event\Queried', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testQueryFailureInvalidURL()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/invalidus',
            'ws_method'  => 'getSystemLog',
            'columns'    => array( 'seven' => 'varchar' ),
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->query(
            $params['user_id'],
            $params['server'],
            $params['ws_method'],
            $params['columns'],
            $params['username'],
            $params['password'],
            $params['title'],
            $params['priority']
        );
        
        $this->assertInternalType( 'string', $response );

        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Soap\SoapAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Soap\Event\QueryFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testQueryFailureInvalidServer()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.211/~dbartkevicius/kompro/test/soap/shit',
            'ws_method'  => 'getSystemLog',
            'columns'    => array( 'seven' => 'varchar' ),
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->query(
            $params['user_id'],
            $params['server'],
            $params['ws_method'],
            $params['columns'],
            $params['username'],
            $params['password'],
            $params['title'],
            $params['priority']
        );
        
        $this->assertInternalType( 'string', $response );

        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Soap\SoapAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Soap\Event\QueryFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testQueryFailureInvalidMethod()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/server',
            'ws_method'  => 'Groovy',
            'columns'    => array( 'seven' => 'varchar' ),
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->query(
            $params['user_id'],
            $params['server'],
            $params['ws_method'],
            $params['columns'],
            $params['username'],
            $params['password'],
            $params['title'],
            $params['priority']
        );
        
        $this->assertInternalType( 'string', $response );

        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Soap\SoapAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Soap\Event\QueryFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testQueryFailureMissingUsername()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/server',
            'ws_method'  => 'getSystemLog',
            'columns'    => array( 'seven' => 'varchar' ),
            'username'   => '',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->query(
            $params['user_id'],
            $params['server'],
            $params['ws_method'],
            $params['columns'],
            $params['username'],
            $params['password'],
            $params['title'],
            $params['priority']
        );
        $this->assertInternalType( 'string', $response );

        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Soap\SoapAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Soap\Event\QueryFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testQueryFailureInvalidColumnType()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/server',
            'ws_method'  => 'getSystemLog',
            'columns'    => array( 'seven' => 'bimbumbah' ),
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->query(
            $params['user_id'],
            $params['server'],
            $params['ws_method'],
            $params['columns'],
            $params['username'],
            $params['password'],
            $params['title'],
            $params['priority']
        );
        
        $this->assertInternalType( 'array', $response );
    }

	
    public function testExportSuccess()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/server',
            'ws_method'  => 'getSystemLog',
            'columns'    => array( 'id' => 'int', 'event_name' => 'varchar' ),
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->query( $params['user_id'], $params['server'], $params['ws_method'], $params['columns'], $params['username'], $params['password'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );

        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Soap\SoapAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Soap\Event\Queried', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
        
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/server',
            'ws_method'  => 'receiveImport',
            'datamap'    => array( 'id' => 'idishke', 'event_name' => 'kasbuvo' ),
            'adapter'    => 'soap',
            'identity'   => $response,
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->export->soap( $params['user_id'], $params['server'], $params['ws_method'], $params['datamap'], $params['adapter'], $params['identity'], $params['username'], $params['password'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );

        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Soap\SoapAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Soap\Event\Exported', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportFailureInvalidIdentity()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/server',
            'ws_method'  => 'receiveImport',
            'datamap'    => array( 'id' => 'idishke', 'event_name' => 'kasbuvo' ),
            'adapter'    => 'soap',
            'identity'   => 'loooool',
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->export->soap( $params['user_id'], $params['server'], $params['ws_method'], $params['datamap'], $params['adapter'], $params['identity'], $params['username'], $params['password'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );

        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Soap\SoapAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Soap\Event\ExportFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportFailureInvalidAdapter()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/server',
            'ws_method'  => 'receiveImport',
            'datamap'    => array( 'id' => 'idishke', 'event_name' => 'kasbuvo' ),
            'adapter'    => 'baws',
            'identity'   => 'fallacy',
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->export->soap( $params['user_id'], $params['server'], $params['ws_method'], $params['datamap'], $params['adapter'], $params['identity'], $params['username'], $params['password'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureEmpty()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/server',
            'ws_method'  => 'receiveImport',
            'datamap'    => array( ),
            'adapter'    => 'baws',
            'identity'   => 'fallacy',
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->export->soap( $params['user_id'], $params['server'], $params['ws_method'], $params['datamap'], $params['adapter'], $params['identity'], $params['username'], $params['password'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureInvalidServer()
    {
        $params = array(
            'user_id'    => 123,
            'server'     => 'http://192.168.0.216/~dbartkevicius/kompro/test/soap/sexver',
            'ws_method'  => 'receiveImport',
            'datamap'    => array( 'id' => 'idishke', 'event_name' => 'kasbuvo' ),
            'adapter'    => 'soap',
            'identity'   => 'RESPONZ',
            'username'   => 'test',
            'password'   => 'test',
            'title'      => 'baws',
            'priority'   => 100
        );
        
        // Part one: enque the command
        $response = $this->export->soap( $params['user_id'], $params['server'], $params['ws_method'], $params['datamap'], $params['adapter'], $params['identity'], $params['username'], $params['password'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Soap\SoapAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Soap\Event\ExportFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    
    public function tearDown()
    {
        $db = Zend_Registry::get( 'db' );
        $db->query( 'TRUNCATE TABLE adapter_soap_queue' );
        $db->query( 'TRUNCATE TABLE soap_adapter' );
        $db->query( 'TRUNCATE TABLE soap_adapter_data' );
        $db->query( 'TRUNCATE TABLE soap_adapter_meta' );
        parent::tearDown();
    }
}