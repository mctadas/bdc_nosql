<?php

class OracleTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');

        require_once( APPLICATION_PATH . '/modules/api/handler/OracleHandler.php');
        require_once( APPLICATION_PATH . '/modules/api/handler/ExportHandler.php');
        
        $this->bootstrap = new Zend_Application( 'staging', APPLICATION_PATH . '/configs/application.ini');
        
        parent::setUp();
        
        $this->handler = new OracleHandler();
        $this->export = new ExportHandler();
    }

    public function testSelectionSuccess()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'Testing',
            'columns'  => array( 'id' => 'int' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\Selected', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Command\Select', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
    }
    
    # Testing if commands are generated successfuly.
    # If passed a table with less than or equal to 1k rows, will fail! 
    public function testSelectionSuccessWithLargeTable()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'mysql_adapter_data',
            'columns'  => array( 'identity' => 'varchar', 'added' => 'date', 'key' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\Selected', $event );
            }
            
            $this->assertGreaterThanOrEqual( 1, count( $unitOfWork->getCommands() ) );
            
            foreach ( $unitOfWork->getCommands() as $command ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Command\Select', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
    }
    
    public function testSelectionFailureInvalidHost()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.1.251',
            'database' => 'kompro',
            'username' => 'kompro',
            'password' => 'orpmok',
            'table'    => 'mysql_adapter_data',
            'columns'  => array( 'identity' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\SelectionFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testSelectionFailureInvalidDatabase()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'labas',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'mysql_adapter_data',
            'columns'  => array( 'identity' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\SelectionFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testSelectionFailureInvalidUsername()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'labas',
            'password' => 'gaumina',
            'table'    => 'mysql_adapter_data',
            'columns'  => array( 'identity' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\SelectionFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testSelectionFailureInvalidPassword()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'labas',
            'table'    => 'mysql_adapter_data',
            'columns'  => array( 'identity' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\SelectionFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testSelectionFailureUnknownTable()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'labas',
            'columns'  => array( 'identity' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\SelectionFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testSelectionFailureInvalidTable()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'mysql_adapter_data`; DROP mysql_adapter_data; SELECT * FROM `mysql_adapter_data',
            'columns'  => array( 'identity' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
    
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\SelectionFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testSelectionFailureEmptyColumns()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'mysql_adapter_data',
            'columns'  => array(),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'array', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNull( $unitOfWork );
    }
    
    
    public function testSelectionFailureInvalidColumnTypes()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'mysql_adapter_data',
            'columns'  => array( 'identity' => 'labas' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'array', $response );
    }

    public function testSelectionFailureInvalidColumns()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'mysql_adapter_data',
            'columns'  => array( 'labas' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\SelectionFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }

	
    public function testSelectionFailureInvalidUserID()
    {
        $params = array(
            'user_id'  => 'labas',
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'mysql_adapter_data',
            'columns'  => array( 'identity' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testSelectionFailureInvalidPort()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'mysql_adapter_data',
            'columns'  => array( 'identity' => 'varchar' ),
            'port'     => 80
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
    
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\SelectionFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testSelectionFailureMissingParameterHost()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'mysql_adapter_data',
            'columns'  => array( 'identity' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testSelectionFailureInvalidPriority()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'adapter_mysql_queue',
            'columns'  => array( 'identity' => 'varchar' ),
            'port'     => 1521,
            'title'    => 'africa',
            'priority' => 'labas'
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportSuccess()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'Testing',
            'columns'  => array( 'id' => 'int', 'value' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\Selected', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Command\Select', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
        
        // Exporting results if above succeeds
        $params = array(
            'user_id'  => 123,
            'identity' => $response,
            'adapter'  => 'oracle',
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'export_table',
            'datamap'  => array(
                'value' => 'item_value'
            ),
            'port'     => 1521,
            'title'    => '',
            'priority' => 100
        );

        // surasyta vienon eiluten, nes tiesiog perduoda paramsus eiles tvarka ir ner cia ko tikrint
        $response = $this->export->oracle( $params['user_id'], $params['identity'], $params['adapter'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['datamap'], $params['port'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\Exported', $event );
            }
            
            // export doesn't create any commands
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportFailureInvalidAdapter()
    {
        // Exporting results if above succeeds
        $params = array(
            'user_id'  => 123,
            'identity' => '123abc',
            'adapter'  => 'labas',
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'export_table',
            'datamap'  => array(
                'value' => 'item_value'
            ),
            'port'     => 1521,
            'title'    => '',
            'priority' => 100
        );

        // surasyta vienon eiluten, nes tiesiog perduoda paramsus eiles tvarka ir ner cia ko tikrint
        $response = $this->export->oracle( $params['user_id'], $params['identity'], $params['adapter'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['datamap'], $params['port'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureMissingDatamap()
    {
        // Exporting results if above succeeds
        $params = array(
            'user_id'  => 123,
            'identity' => '123abc',
            'adapter'  => 'oracle',
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'export_table',
            'datamap'  => array(),
            'port'     => 1521,
            'title'    => '',
            'priority' => 100
        );

        // surasyta vienon eiluten, nes tiesiog perduoda paramsus eiles tvarka ir ner cia ko tikrint
        $response = $this->export->oracle( $params['user_id'], $params['identity'], $params['adapter'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['datamap'], $params['port'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureInvalidHost()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'Testing',
            'columns'  => array( 'id' => 'int', 'value' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\Selected', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Command\Select', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
        
        // Exporting results if above succeeds
        $params = array(
            'user_id'  => 123,
            'identity' => $response,
            'adapter'  => 'oracle',
            'host'     => '192.168.0.205',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'export_table',
            'datamap'  => array(
                'value' => 'item_value'
            ),
            'port'     => 1521,
            'title'    => '',
            'priority' => 100
        );

        // surasyta vienon eiluten, nes tiesiog perduoda paramsus eiles tvarka ir ner cia ko tikrint
        $response = $this->export->oracle( $params['user_id'], $params['identity'], $params['adapter'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['datamap'], $params['port'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\ExportFailed', $event );
            }
            
            // export doesn't create any commands
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportFailureInvalidDatamap()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'Testing',
            'columns'  => array( 'id' => 'int', 'value' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\Selected', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Command\Select', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
        
        // Exporting results if above succeeds
        $params = array(
            'user_id'  => 123,
            'identity' => $response,
            'adapter'  => 'oracle',
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'export_table',
            'datamap'  => array(
                'labas' => 'labas'
            ),
            'port'     => 1521,
            'title'    => '',
            'priority' => 100
        );

        // surasyta vienon eiluten, nes tiesiog perduoda paramsus eiles tvarka ir ner cia ko tikrint
        $response = $this->export->oracle( $params['user_id'], $params['identity'], $params['adapter'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['datamap'], $params['port'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\ExportFailed', $event );
            }
            
            // export doesn't create any commands
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }

	
    public function testExportFailureInvalidPassword()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'gaumina',
            'table'    => 'Testing',
            'columns'  => array( 'id' => 'int', 'value' => 'varchar' ),
            'port'     => 1521
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\Selected', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Command\Select', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
        
        // Exporting results if above succeeds
        $params = array(
            'user_id'  => 123,
            'identity' => $response,
            'adapter'  => 'oracle',
            'host'     => '192.168.0.202',
            'database' => 'XE',
            'username' => 'gaumina',
            'password' => 'labas',
            'table'    => 'export_table',
            'datamap'  => array(
                'value' => 'item_value'
            ),
            'port'     => 1521,
            'title'    => '',
            'priority' => 100
        );

        // surasyta vienon eiluten, nes tiesiog perduoda paramsus eiles tvarka ir ner cia ko tikrint
        $response = $this->export->oracle( $params['user_id'], $params['identity'], $params['adapter'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['datamap'], $params['port'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\ExportFailed', $event );
            }
            
            // export doesn't create any commands
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testSelectionFailureWithMySQLServer()
    {
        $params = array(
            'user_id'  => 123,
            'host'     => '192.168.0.201',
            'database' => 'kompro',
            'username' => 'kompro',
            'password' => 'orpmok',
            'table'    => 'exported_files',
            'columns'  => array( 'size' => 'int' ),
            'port'     => 3306
        );
        
        // Part one: enque the command
        $response = $this->handler->select( $params['user_id'], $params['host'], $params['database'], $params['username'], $params['password'], $params['table'], $params['columns'], $params['port'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Oracle\OracleAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Oracle\Event\SelectionFailed', $event );
            }
        }
    }
    
    
    public function tearDown()
    {
        $db = Zend_Registry::get( 'db' );
        $db->query( 'TRUNCATE TABLE adapter_oracle_queue' );
        $db->query( 'TRUNCATE TABLE oracle_adapter' );
        $db->query( 'TRUNCATE TABLE oracle_adapter_data' );
        $db->query( 'TRUNCATE TABLE oracle_adapter_meta' );
        parent::tearDown();
    }
}