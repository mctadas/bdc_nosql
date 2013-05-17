<?php

class PDFTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');

        require_once( APPLICATION_PATH . '/modules/api/handler/ExportHandler.php');
        
        $this->bootstrap = new Zend_Application( 'staging', APPLICATION_PATH . '/configs/application.ini');
        
        parent::setUp();
        
        $this->export  = new ExportHandler();
    }
    
    public function testExportSuccess()
    {
        $params = array(
            'user_id'         => 123,
            'imageurl'        => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/test.jpg',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->export->pdf( $params['user_id'], $params['imageurl'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\PDF\PDFAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\PDF\Event\Exported', $event );
                
                if ( isset( $event->exportedFile->attributes->value->identity ) ) {
                    
                    @unlink( APPLICATION_PATH . '/exported/' . $event->exportedFile->attributes->value->identity );
                }
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportFailureMissingImageURL()
    {
        $params = array(
            'user_id'         => 123,
            'title'           => 'title',
            'priority'        => 100,
        );
        
        $response = $this->export->pdf( $params['user_id'], null, $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testExportFailureInvalidImageURL()
    {
        $params = array(
            'user_id'         => 123,
            'imageurl'        => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/blahahahaekasdasl.jpg',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->export->pdf( $params['user_id'], $params['imageurl'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\PDF\PDFAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\PDF\Event\ExportFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportFailureForbiddenImageURL()
    {
        $params = array(
            'user_id'         => 123,
            'imageurl'        => 'http://php53.gaumina.lan/~dbartkevicius/kompro/.htaccess',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        // Part one: enque the command
        $response = $this->export->pdf( $params['user_id'], $params['imageurl'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\PDF\PDFAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\PDF\Event\ExportFailed', $event );
            }
            
            $this->assertEquals( 0, count( $unitOfWork->getCommands() ) );
        }
    }
    
    public function testExportFailureMissingUserID()
    {
        $params = array(
            'user_id'         => null,
            'imageurl'        => 'http://php53.gaumina.lan/~dbartkevicius/kompro_testfiles/blahahahaekasdasl.jpg',
            'title'           => 'title',
            'priority'        => 100,
        );
        
        $response = $this->export->pdf( $params['user_id'], $params['imageurl'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function tearDown()
    {
        $db = Zend_Registry::get( 'db' );
        $db->query( 'TRUNCATE TABLE adapter_pdf_queue' );
        $db->query( 'TRUNCATE TABLE pdf_adapter' );
        $db->query( 'TRUNCATE TABLE pdf_adapter_data' );
        $db->query( 'TRUNCATE TABLE exported_files' );
        parent::tearDown();
    }
}