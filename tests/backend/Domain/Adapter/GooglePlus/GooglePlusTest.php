<?php

class GooglePlusTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');

        require_once( APPLICATION_PATH . '/modules/api/handler/GooglePlusHandler.php');
        
        $this->bootstrap = new Zend_Application( 'staging', APPLICATION_PATH . '/configs/application.ini');
        
        parent::setUp();
        
        $this->handler = new GooglePlusHandler();
    }
    
    public function testFindMentionSuccess()
    {
        $params = array(
            'user_id'     => 123,
            'query'       => 'test',
            'max_results' => 20,
            'language'    => '',
            'title'       => 'baws',
            'priority'    => 2
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['max_results'], $params['language'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\GooglePlus\Event\MentionFound', $event );
            }
        }
    }
    
    public function testFindMentionFailureInvalidData()
    {
        $params = array(
            'user_id'     => 123,
            'query'       => 'labas',
            'max_results' => -401,
            'language'    => '',
            'title'       => 'baws',
            'priority'    => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['max_results'], $params['language'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\GooglePlus\Event\MentionFoundFailed', $event );
            }
        }
    }
    
    public function testFindMentionFailureInvalidQuery()
    {
        $params = array(
            'user_id'     => 123,
            'query'       => '',
            'max_results' => 20,
            'language'    => '',
            'title'       => 'baws',
            'priority'    => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['max_results'], $params['language'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\GooglePlus\Event\MentionFoundFailed', $event );
            }
        }
    }
    
    public function testFindMentionFailureInvalidAPIResponse()
    {
        $params = array(
            'user_id'     => 123,
            'query'       => 'test',
            'max_results' => 20,
            'language'    => '',
            'title'       => 'baws',
            'priority'    => 2
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['max_results'], $params['language'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $adapter->_api->apiUrl = 'http://www.gaumina.lt';
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\GooglePlus\Event\MentionFoundFailed', $event );
            }
        }
    }
    
    public function testFindMentionFailureInvalidAPIURL()
    {
        $params = array(
            'user_id'     => 123,
            'query'       => 'test',
            'max_results' => 20,
            'language'    => '',
            'title'       => 'baws',
            'priority'    => 2
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['max_results'], $params['language'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $adapter->_api->apiUrl = 'http://www.unknownaddress.lt';
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\GooglePlus\Event\MentionFoundFailed', $event );
            }
        }
    }

	
    public function testRetrieveCommentsSuccess()
    {
        $params = array(
            'user_id'     => 123,
            'activity_id' => 'z125cdsyrkudjhgzv04cetygzsy1d5vx5dg',
            'max_results' => 20,
            'sort_order'  => '',
            'title'       => 'baws',
            'priority'    => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->comments( $params['user_id'], $params['activity_id'], $params['max_results'], $params['sort_order'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\GooglePlus\Event\CommentsRetrieved', $event );
            }
        }
    }
    
    public function testRetrieveCommentsFailureInvalidActivityID()
    {
        $params = array(
            'user_id'     => 123,
            'activity_id' => 'title title',
            'max_results' => 20,
            'sort_order'  => '',
            'title'       => 'baws',
            'priority'    => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->comments( $params['user_id'], $params['activity_id'], $params['max_results'], $params['sort_order'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\GooglePlus\Event\CommentsRetrievationFailed', $event );
            }
        }
    }
    
    public function testRetrieveCommentsFailureNonExistantActivityID()
    {
        $params = array(
            'user_id'     => 123,
            'activity_id' => 'title',
            'max_results' => 20,
            'sort_order'  => '',
            'title'       => 'baws',
            'priority'    => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->comments( $params['user_id'], $params['activity_id'], $params['max_results'], $params['sort_order'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\GooglePlus\Event\CommentsRetrievationFailed', $event );
            }
        }
    }
    
    public function testFindUsersSuccess()
    {
        $params = array(
            'user_id'     => 123,
            'query'       => 'labas',
            'max_results' => 20,
            'language'    => '',
            'title'       => 'baws',
            'priority'    => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->users( $params['user_id'], $params['query'], $params['max_results'], $params['language'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\GooglePlus\Event\UsersFound', $event );
            }
        }
    }
    
    public function testFindUsersFailureInvalidQuery()
    {
        $params = array(
            'user_id'     => 123,
            'query'       => '',
            'max_results' => 20,
            'language'    => '',
            'title'       => 'baws',
            'priority'    => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->users( $params['user_id'], $params['query'], $params['max_results'], $params['language'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        $this->assertInstanceOf( 'Bb4w\Domain\UnitOfWork', $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\GooglePlus\Event\UsersFoundFailed', $event );
            }
        }
    }
    
    public function testFindUsersFailureInvalidUserID()
    {
        $params = array(
            'user_id'     => 'labas',
            'query'       => 'labas',
            'max_results' => 20,
            'language'    => '',
            'title'       => 'baws',
            'priority'    => 100
        );
        
        // Part one: enque the command
        $response = $this->handler->users( $params['user_id'], $params['query'], $params['max_results'], $params['language'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNull( $unitOfWork );
    }
    
    public function testFindUsersFailureInvalidPriority()
    {
        $params = array(
            'user_id'     => 123,
            'query'       => 'labas',
            'max_results' => 20,
            'language'    => '',
            'title'       => 'baws',
            'priority'    => 'simtas'
        );
        
        // Part one: enque the command
        $response = $this->handler->users( $params['user_id'], $params['query'], $params['max_results'], $params['language'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\GooglePlus\GooglePlusAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNull( $unitOfWork );
    }
    
    
    public function tearDown()
    {
        $db = Zend_Registry::get( 'db' );
        $db->query( 'TRUNCATE TABLE adapter_googleplus_queue' );
        $db->query( 'TRUNCATE TABLE googleplus_adapter' );
        $db->query( 'TRUNCATE TABLE googleplus_adapter_data' );
        $db->query( 'TRUNCATE TABLE googleplus_adapter_meta' );
        parent::tearDown();
    }
}