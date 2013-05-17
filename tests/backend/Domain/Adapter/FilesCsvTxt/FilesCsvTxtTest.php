<?php

class FilesCsvTxtTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');

        require_once( APPLICATION_PATH . '/modules/api/handler/FilesCsvTxtHandler.php');
        require_once( APPLICATION_PATH . '/modules/api/handler/ExportHandler.php');
        
        $this->bootstrap = new Zend_Application( 'staging', APPLICATION_PATH . '/configs/application.ini');
        
        parent::setUp();
        
        $this->handler = new FilesCsvTxtHandler();
        $this->export  = new ExportHandler();
    }
    
    public function testParseFileSuccess()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/recipes.csv',
            'columns'         => array( 'Recipe ID' => 'varchar', 'Recipe Title' => 'varchar' ),
            'skip_first_line' => '0',
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 100,
        );

        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );

        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesCsvTxt\FilesCsvTxtAdapter();
        $unitOfWork = $adapter->proceed();

        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );

        if ( !empty( $unitOfWork ) ) {

            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Event\FileParsed', $event );
            }

            foreach ( $unitOfWork->getCommands() as $command ) {

                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Command\ParseFile', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
    }
    
    # will fail with a file that has less than 1001 lines.
    public function testParseFileSuccessLargeFile()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/20klines.csv',
            'columns'         => array( 'Letter' => 'varchar', 'Gender' => 'varchar', 'Alternate' => 'varchar' ),
            'skip_first_line' => '0',
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesCsvTxt\FilesCsvTxtAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Event\FileParsed', $event );
            }
            
            $this->assertGreaterThanOrEqual( 1, count( $unitOfWork->getCommands() ) );
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Command\ParseFile', $command );
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
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesCsvTxt\FilesCsvTxtAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Event\FileParseFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testParseFileFailureColumnsType()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/20klines.csv',
            'columns'         => array( 'column' => 'labas' ),
            'skip_first_line' => '0',
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testParseFileSuccessIncorrectDelimiter()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/recipes.csv',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'delimiter'       => ',',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesCsvTxt\FilesCsvTxtAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Event\FileParseFailed', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Command\ParseFile', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
    }
    
    public function testParseFileSuccessInvalidFileXlsx()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/test.xlsx',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesCsvTxt\FilesCsvTxtAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Event\FileParseFailed', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Command\ParseFile', $command );
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
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesCsvTxt\FilesCsvTxtAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Event\FileParseFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testParseFileFailureEmptyFile()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/empty.csv',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesCsvTxt\FilesCsvTxtAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Event\FileParseFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testParseFileFailureInvalidLocalURL()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'index.php',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testParseFileFailureInvalidUserID()
    {
        $params = array(
            'user_id'         => 'labas',
            'path'            => 'http://www.lt',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testParseFileFailureInvalidPriority()
    {
        $params = array(
            'user_id'         => 1234,
            'path'            => 'http://www.lt',
            'columns'         => array( 'column' => 'varchar' ),
            'skip_first_line' => '0',
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 'labas',
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testParseFileFailureInvalidDelimiter()
    {
        $params = array(
            'user_id'         => 1234,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/3klines.csv',
            'columns'         => array( 'Letter' => 'varchar', 'Gender' => 'varchar', 'Alternate' => 'varchar' ),
            'skip_first_line' => '0',
            'delimiter'       => '@',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }

	
    public function testExportSuccess()
    {
        $params = array(
            'user_id'         => 123,
            'path'            => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/recipes.csv',
            'columns'         => array( 'Recipe ID' => 'varchar', 'Recipe Title' => 'varchar' ),
            'skip_first_line' => '0',
            'delimiter'       => ';',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->handler->parsefile( $params['user_id'], $params['path'], $params['columns'], $params['delimiter'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesCsvTxt\FilesCsvTxtAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Event\FileParsed', $event );
            }
            
            foreach ( $unitOfWork->getCommands() as $command ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Command\ParseFile', $command );
                $this->assertEquals( 100, $command->priority->value );
            }
        }
        
        $params = array(
            'user_id'  => 123,
            'adapter'  => 'filescsvtxt',
            'identity' => $response,
            'file_ext' => 'csv',
            'title'    => 'title',
            'priority' => 100,
        );
        
        // Part one: enque the command
        $response = $this->export->csvtxt( $params['user_id'], $params['adapter'], $params['identity'], $params['file_ext'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesCsvTxt\FilesCsvTxtAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Event\Exported', $event );
                
                if ( isset( $event->exportedFile->attributes->value->identity ) ) {
                    
                    @unlink( APPLICATION_PATH . '/exported/' . $event->exportedFile->attributes->value->identity );
                }
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportFailureInvalidIdentity()
    {
        $params = array(
            'user_id'  => 123,
            'adapter'  => 'filescsvtxt',
            'identity' => 'lol',
            'file_ext' => 'csv',
            'title'    => 'title',
            'priority' => 100,
        );
        
        // Part one: enque the command
        $response = $this->export->csvtxt( $params['user_id'], $params['adapter'], $params['identity'], $params['file_ext'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\FilesCsvTxt\FilesCsvTxtAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\FilesCsvTxt\Event\ExportFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportFailureInvalidAdapter()
    {
        $params = array(
            'user_id'  => 123,
            'adapter'  => 'lol',
            'identity' => 'lol',
            'file_ext' => 'csv',
            'title'    => 'title',
            'priority' => 100,
        );
        
        // Part one: enque the command
        $response = $this->export->csvtxt( $params['user_id'], $params['adapter'], $params['identity'], $params['file_ext'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureInvalidFileExt()
    {
        $params = array(
            'user_id'  => 123,
            'adapter'  => 'filecsvtxt',
            'identity' => 'lol',
            'file_ext' => 'lol',
            'title'    => 'title',
            'priority' => 100,
        );
        
        // Part one: enque the command
        $response = $this->export->csvtxt( $params['user_id'], $params['adapter'], $params['identity'], $params['file_ext'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureMissingIdentity()
    {
        $params = array(
            'user_id'  => 123,
            'adapter'  => 'filecsvtxt',
            'identity' => '',
            'file_ext' => 'lol',
            'title'    => 'title',
            'priority' => 100,
        );
        
        // Part one: enque the command
        $response = $this->export->csvtxt( $params['user_id'], $params['adapter'], $params['identity'], $params['file_ext'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureMissingAdapter()
    {
        $params = array(
            'user_id'  => 123,
            'adapter'  => '',
            'identity' => 'll',
            'file_ext' => 'lol',
            'title'    => 'title',
            'priority' => 100,
        );
        
        // Part one: enque the command
        $response = $this->export->csvtxt( $params['user_id'], $params['adapter'], $params['identity'], $params['file_ext'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureMissingFileExt()
    {
        $params = array(
            'user_id'  => 123,
            'adapter'  => 'filecsvtxt',
            'identity' => 'aa',
            'file_ext' => '',
            'title'    => 'title',
            'priority' => 100,
        );
        
        // Part one: enque the command
        $response = $this->export->csvtxt( $params['user_id'], $params['adapter'], $params['identity'], $params['file_ext'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    
    public function tearDown()
    {
        $db = Zend_Registry::get( 'db' );
        $db->query( 'TRUNCATE TABLE adapter_filescsvtxt_queue' );
        $db->query( 'TRUNCATE TABLE filescsvtxt_adapter' );
        $db->query( 'TRUNCATE TABLE filescsvtxt_adapter_data' );
        $db->query( 'TRUNCATE TABLE filescsvtxt_adapter_meta' );
        $db->query( 'TRUNCATE TABLE exported_files' );
        parent::tearDown();
    }
}