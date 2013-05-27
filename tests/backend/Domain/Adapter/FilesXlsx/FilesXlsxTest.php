<?php

class FilesXlsxTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');

        require_once( APPLICATION_PATH . '/modules/api/handler/FilesXlsxHandler.php');
        require_once( APPLICATION_PATH . '/modules/api/handler/ExportHandler.php');
        
        $this->bootstrap = new Zend_Application( 'staging', APPLICATION_PATH . '/configs/application.ini');
        
        parent::setUp();
        
        $this->handler = new FilesXlsxHandler();
        $this->export  = new ExportHandler();
    }

    public function testParseFileSuccess()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/test.xlsx',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\FileParsed', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Command\ParseFile', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
    }
    
    # will fail with a file that has less than 101 lines.
    public function testParseFileSuccessLargeFile()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/fr.xlsx',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\FileParsed', $event );
            }
            
            $this->assertGreaterThanOrEqual( 1, count( $unitOfWork->getCommands() ) );
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Command\ParseFile', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
    }

	
    public function testParseFileSuccessSkipFirstLine()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/test.xlsx',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '1',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\FileParsed', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Command\ParseFile', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
    }


    public function testParseFileFailureInvalidURL()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://dev.gaumina.lan/~dbartkeviciux',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\FileParseFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }

    public function testParseFileFailureColumnsType()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/test.xlsx',
            'columns'         => array( 'column' => 'labas' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testParseFileSuccessWithCsvFile()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/3klines.csv',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\FileParsed', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Command\ParseFile', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
    }
    
    public function testParseFileFailureForbiddenURL()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://192.168.0.216/~dbartkevicius/kompro/.htaccess',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\FileParseFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testParseFileFailureEmptyFile()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://dev.gaumina.lan/~dbartkevicius/invalid.xlsx',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\FileParseFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testParseFileFailureZeroLinesFile()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/empty.xlsx',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '1',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\FileParseFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testParseFileFailureMissingColumns()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://www.labas.lt/xlsx.xlsx',
            'columns'         => array(),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }

    public function testParseFileFailureMissingPath()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => '',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
	
    public function testParseFileFailureInvalidLocalURL()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'index.php',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    
    public function testParseFileFailureInvalidUserID()
    {
        $params = array(
            'user_id'         => 'labas',
            'path'            => 'http://www.lt',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testParseFileFailureInvalidPriority()
    {
        $params = array(
            'user_id'         => 1234,
            'path'            => 'http://www.lt',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 'labas',
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportSuccess()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/test.xlsx',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['skip_first_line'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\FileParsed', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Command\ParseFile', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
        
        $params = array(
            'user_id'  => 123,
            'adapter'  => 'filesxlsx',
            'identity' => $response,
            'title'    => 'lol'
        );
        
        $response = $this->export->xlsx( $params['user_id'], $params['adapter'], $params['identity'], $params['title'] );
        $this->assertInternalType( 'string', $response );
        
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\Exported', $event );
                
                if ( isset( $event->exportedFile->attributes->value->identity ) ) {
                    
                    @unlink( APPLICATION_PATH . '/exported/' . $event->exportedFile->attributes->value->identity );
                }
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportDocxSuccess()
    {   
        $params = array(
            'user_id'  => 123,
            'imageurl' => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/test.jpg',
            'title'    => 'lol'
        );
        
        $response = $this->export->docx( $params['user_id'], $params['imageurl'], $params['title'] );
        $this->assertInternalType( 'string', $response );
        
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\Exported', $event );
                
                if ( isset( $event->exportedFile->attributes->value->identity ) ) {
                    
                    @unlink( APPLICATION_PATH . '/exported/' . $event->exportedFile->attributes->value->identity );
                }
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportDocxFailureInvalidImageURL()
    {   
        $params = array(
            'user_id'  => 123,
            'imageurl' => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/missing.jpg',
            'title'    => 'lol'
        );
        
        $response = $this->export->docx( $params['user_id'], $params['imageurl'], $params['title'] );
        $this->assertInternalType( 'string', $response );
        
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\ExportFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportDocxFailureForbiddenImageURL()
    {   
        $params = array(
            'user_id'  => 123,
            'imageurl' => 'http://192.168.0.216/~dbartkevicius/kompro/.htaccess',
            'title'    => 'lol'
        );
        
        $response = $this->export->docx( $params['user_id'], $params['imageurl'], $params['title'] );
        $this->assertInternalType( 'string', $response );
        
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\ExportFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportDocxFailureMissingImageURL()
    {   
        $params = array(
            'user_id'  => 123,
            'imageurl' => '',
            'title'    => 'lol'
        );
        
        $response = $this->export->docx( $params['user_id'], $params['imageurl'], $params['title'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureInvalidIdentity()
    {
        $params = array(
            'user_id'  => 123,
            'adapter'  => 'filesxlsx',
            'identity' => 'invalid identity',
            'title'    => 'lol'
        );
        
        $response = $this->export->xlsx( $params['user_id'], $params['adapter'], $params['identity'], $params['title'] );
        $this->assertInternalType( 'string', $response );
        
        $adapter = new Domain\Adapter\FilesXlsx\FilesXlsxAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesXlsx\Event\ExportFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportFailureInvalidAdapter()
    {
        $params = array(
            'user_id'  => 123,
            'adapter'  => 'labas',
            'identity' => 'invalid identity',
            'title'    => 'lol'
        );
        
        $response = $this->export->xlsx( $params['user_id'], $params['adapter'], $params['identity'], $params['title'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureMissingAdapter()
    {
        $params = array(
            'user_id'  => 123,
            'adapter'  => '',
            'identity' => 'invalid identity',
            'title'    => 'lol'
        );
        
        $response = $this->export->xlsx( $params['user_id'], $params['adapter'], $params['identity'], $params['title'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureMissingIdentity()
    {
        $params = array(
            'user_id'  => 123,
            'adapter'  => 'progress',
            'identity' => '',
            'title'    => ''
        );
        
        $response = $this->export->xlsx( $params['user_id'], $params['adapter'], $params['identity'], $params['title'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureMissingUserID()
    {
        $params = array(
            'user_id'  => '',
            'adapter'  => 'progress',
            'identity' => 'xc',
            'title'    => ''
        );
        
        $response = $this->export->xlsx( $params['user_id'], $params['adapter'], $params['identity'], $params['title'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function tearDown()
    {
        $db = Zend_Registry::get( 'db' );
        $db->query( 'TRUNCATE TABLE adapter_filesxlsx_queue' );
        $db->query( 'TRUNCATE TABLE filesxlsx_adapter' );
        $db->query( 'TRUNCATE TABLE filesxlsx_adapter_data' );
        $db->query( 'TRUNCATE TABLE filesxlsx_adapter_meta' );
        parent::tearDown();
    }
}