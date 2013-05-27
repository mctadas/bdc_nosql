<?php

class FacebookTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
        
        require_once( APPLICATION_PATH . '/modules/api/handler/FacebookHandler.php');
        
        $this->bootstrap = new Zend_Application( 'staging', APPLICATION_PATH . '/configs/application.ini');
        
        parent::setUp();
        
        $this->handler = new FacebookHandler();
    }
    
    public function testFindMentionSuccess()
    {
        $params = array(
            'priority' => 100,
            'query'    => 'a',
            'user_id'  => 123,
            'title'    => 'test',
            'since'    => strtotime( '-10 days' ),
            'lang'     => 'lt'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['since'], $params['lang'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Facebook\FacebookAdapter();
        $unitOfWork = $adapter->proceed();
        
        foreach ( $unitOfWork->getEvents() as $event ) {
            
            $this->assertInstanceOf( 'Domain\Adapter\Facebook\Event\MentionFound', $event );
        }
    }
    
    public function testFindMentionFailureInvalidData()
    {
        $params = array(
            'priority' => 100,
            'query'    => 'test',
            'user_id'  => 123,
            'title'    => 'test',
            'since'    => strtotime( '+10 days' ),
            'lang'     => 'lt'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['since'], $params['lang'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Facebook\FacebookAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Facebook\Event\MentionFoundFailed', $event );
            }
        }
    }
    
    public function testFindMentionFailureInvalidQuery()
    {
        $params = array(
            'priority' => 100,
            'query'    => '',
            'user_id'  => 123,
            'title'    => 'test',
            'since'    => (time() - 864000),
            'lang'     => 'lt'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['since'], $params['lang'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Facebook\FacebookAdapter();
        $unitOfWork = $adapter->proceed();
        
        // Invalid command was queued, unit of work should contain error event
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
        
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Facebook\Event\MentionFoundFailed', $event );
            }
        }
    }
    
    public function testFindMentionFailureInvalidUserID()
    {
        $params = array(
            'priority' => 100,
            'query'    => 'test',
            'user_id'  => 'aa',
            'title'    => 'test',
            'since'    => (time() - 864000),
            'lang'     => 'lt'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['since'], $params['lang'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'array', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Facebook\FacebookAdapter();
        $unitOfWork = $adapter->proceed();
        
        // No command was queued, the unit of work should be NULL
        $this->assertNull( $unitOfWork );
    }
    
    public function testFindMentionFailureInvalidAPIResponse()
    {
        $params = array(
            'priority' => 100,
            'query'    => 'a',
            'user_id'  => 123,
            'title'    => 'test',
            'since'    => strtotime( '-10 days' ),
            'lang'     => 'lt'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['since'], $params['lang'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Facebook\FacebookAdapter();
        $adapter->_api->graphUri = 'http://www.gaumina.lt';
        
        $unitOfWork = $adapter->proceed();
        
        foreach ( $unitOfWork->getEvents() as $event ) {
            
            $this->assertInstanceOf( 'Domain\Adapter\Facebook\Event\MentionFoundFailed', $event );
        }
    }
    
    public function testFindMentionFailureInvalidAPIURL()
    {
        $params = array(
            'priority' => 100,
            'query'    => 'a',
            'user_id'  => 123,
            'title'    => 'test',
            'since'    => strtotime( '-10 days' ),
            'lang'     => 'lt'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['since'], $params['lang'], $params['title'], $params['priority'] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Facebook\FacebookAdapter();
        $adapter->_api->graphUri = 'http://www.teddyisreallll.lt';
        
        $unitOfWork = $adapter->proceed();
        
        foreach ( $unitOfWork->getEvents() as $event ) {
            
            $this->assertInstanceOf( 'Domain\Adapter\Facebook\Event\MentionFoundFailed', $event );
        }
    }

	
    public function testCreateEventCommandSuccess()
    {
        $params = array(
            'priority'     => 100,
            'user_id'      => 125,
            'access_token' => 'acas',
            'start_time'   => time(),
            'end_time'     => time(),
            'owner'        => 1,
            'privacy_type' => 'open',
            'location'     => 'vilnius',
            'description'  => 'test',
            'title'        => 'test',
            'name'         => 'a',
        );
        
        $response = $this->handler->event(
                $params['user_id'], $params['access_token'], $params['start_time'],
                $params['name'], $params['end_time'], $params['owner'], $params['privacy_type'],
                $params['title'], $params['location'], $params['description'], $params['priority']
        );
        $this->assertInternalType( 'string', $response );
    }
    
    public function testCreateEventCommandFailureInvalidData()
    {
        $params = array(
            'priority'     => 100,
            'user_id'      => 125,
            'access_token' => 'acca',
            'start_time'   => '',
            'end_time'     => '',
            'owner'        => '',
            'privacy_type' => '',
            'location'     => '',
            'description'  => '',
            'title'        => '',
            'name'         => '',
        );
        
        $response = $this->handler->event(
                $params['user_id'], $params['access_token'], $params['start_time'],
                $params['name'], $params['end_time'], $params['owner'], $params['privacy_type'],
                $params['title'], $params['location'], $params['description'], $params['priority']
        );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testCreatePostCommandSuccess()
    {
        $params = array(
            'priority'     => 100,
            'user_id'      => 125,
            'access_token' => 'acc_tkn',
            'page_id'      => '1',
            'message'      => 'a',
            'link'         => '',
            'picture'      => '',
            'name'         => 'a',
            'title'        => 'a',
            'caption'      => 'a',
            'description'  => 'a',
        );
        
        $response = $this->handler->post(
                $params['user_id'], $params['access_token'], $params['page_id'],
                $params['message'], $params['link'], $params['picture'], $params['name'],
                $params['title'], $params['caption'], $params['description'], $params['priority']
        );
        $this->assertInternalType( 'string', $response );
    }
    
    public function testCreatePostCommandFailureInvalidData()
    {
        $params = array(
            'priority'     => 100,
            'user_id'      => 125,
            'access_token' => '',
            'page_id'      => '',
            'message'      => '',
            'link'         => '',
            'picture'      => '',
            'name'         => '',
            'title'        => '',
            'caption'      => '',
            'description'  => '',
        );
        
        $response = $this->handler->post(
                $params['user_id'], $params['access_token'], $params['page_id'],
                $params['message'], $params['link'], $params['picture'], $params['name'],
                $params['title'], $params['caption'], $params['description'], $params['priority']
        );
        $this->assertInternalType( 'array', $response );
    }
    
    
    public function tearDown()
    {
        $db = Zend_Registry::get( 'db' );
        $db->query( 'TRUNCATE TABLE adapter_facebook_queue' );
        $db->query( 'TRUNCATE TABLE facebook_adapter' );
        $db->query( 'TRUNCATE TABLE facebook_adapter_data' );
        $db->query( 'TRUNCATE TABLE facebook_adapter_meta' );
        parent::tearDown();
    }
}