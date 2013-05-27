<?php

class TwitterTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');

        $this->bootstrap = new Zend_Application( 'staging', APPLICATION_PATH . '/configs/application.ini');
        
        parent::setUp();
        
        require_once( APPLICATION_PATH . '/modules/api/handler/TwitterHandler.php');
        
        $this->handler = new TwitterHandler();
    }
    
    public function testFindMentionSuccess()
    {
        $params = array(
            'priority' => 100,
            'query'    => 'morning',
            'user_id'  => 123,
            'title'    => 'test',
            'rpp'      => 25,
            'page'     => 1,
            'lang'     => 'lt'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['rpp'], $params['page'], $params['lang'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Twitter\TwitterAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {

            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Twitter\Event\MentionFound', $event );
            }
        }
    }
    
    public function testFindMentionFailureInvalidParameters()
    {
        $params = array(
            'priority' => 100,
            'query'    => 'invalid page size test',
            'user_id'  => 1,
            'title'    => 'test',       
            'rpp'      => -25,
            'page'     => -1,
            'lang'     => 'not existing lang'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['rpp'], $params['page'], $params['lang'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Twitter\TwitterAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Twitter\Event\MentionFoundFailed', $event );
            }
        }
    }
    
    public function testFindMentionFailureInvalidUserID()
    {
        $params = array(
            'priority' => 100,
            'query'    => 'invalid page size test',
            'user_id'  => 'invalid user id',
            'title'    => 'test',
            'rpp'      => -25,
            'page'     => -1,
            'lang'     => 'not existing lang'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['rpp'], $params['page'], $params['lang'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'array', $response );
    }
    
    public function testFindMentionFailureInvalidQuery()
    {
        $params = array(
            'priority' => 100,
            'query'    => '',
            'user_id'  => 1,
            'title'    => 'test',
            'rpp'      => -25,
            'page'     => -1,
            'lang'     => 'not existing lang'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['rpp'], $params['page'], $params['lang'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Twitter\TwitterAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {

            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Twitter\Event\MentionFoundFailed', $event );
            }
        }
    }
    
    public function testFindMentionFailureInvalidAPIResponse()
    {
        $params = array(
            'priority' => 100,
            'query'    => 'lol',
            'user_id'  => 1,
            'title'    => 'test',
            'rpp'      => 10,
            'page'     => 1,
            'lang'     => 'not existing lang'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['rpp'], $params['page'], $params['lang'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Twitter\TwitterAdapter();
        $adapter->_search->setUri( 'http://www.gaumina.lt' );
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Twitter\Event\MentionFoundFailed', $event );
            }
        }
    }
    
    public function testFindMentionFailureAPIUnavailable()
    {
        $params = array(
            'priority' => 100,
            'query'    => 'lol',
            'user_id'  => 1,
            'title'    => 'test',
            'rpp'      => 10,
            'page'     => 1,
            'lang'     => 'not existing lang'
        );
        
        // Part one: enque the command
        $response = $this->handler->search( $params['user_id'], $params['query'], $params['rpp'], $params['page'], $params['lang'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Twitter\TwitterAdapter();
        $adapter->_search->setUri( 'http://www.notexistinguri.lt' );
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Twitter\Event\MentionFoundFailed', $event );
            }
        }
    }

	
    public function testReceiveTrendsSuccess()
    {
        $params = array(
            'priority' => 100,
            'title'    => 'test',
            'user_id'  => 123,
            'date'     => date('Y-m-d')
        );
        
        // Part one: enque the command
        $response = $this->handler->trends( $params['user_id'], $params['date'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Twitter\TwitterAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Twitter\Event\TrendsReceived', $event );
            }
        }
    }
    
    public function testReceiveTrendsFailureInvalidData()
    {
        $params = array(
            'priority' => 100,
            'title'    => 'like a boss',
            'user_id'  => 123,
            'date'     => '2012-05-05'
        );
        
        // Part one: enque the command
        $response = $this->handler->trends( $params['user_id'], $params['date'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Twitter\TwitterAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {

            foreach ( $unitOfWork->getEvents() as $event ) {

                $this->assertInstanceOf( 'Domain\Adapter\Twitter\Event\TrendsReceivationFailed', $event );
            }
        }
    }
    
    public function testReceiveTrendsFailureInvalidUserID()
    {
        $params = array(
            'priority' => 100,
            'title'    => 'like a boss',
            'user_id'  => 'boss',
            'date'     => '2012-06-01'
        );
        
        // Part one: enque the command
        $response = $this->handler->trends( $params['user_id'], $params['date'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'array', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Twitter\TwitterAdapter();
        $unitOfWork = $adapter->proceed();
        
        $this->assertNull( $unitOfWork );
    }

    public function testReceiveTrendsFailureInvalidAPIResponse()
    {
        $params = array(
            'priority' => 100,
            'title'    => 'test',
            'user_id'  => 123,
            'date'     => date('Y-m-d')
        );
        
        // Part one: enque the command
        $response = $this->handler->trends( $params['user_id'], $params['date'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Twitter\TwitterAdapter();
        $adapter->_trends->setUri( 'http://www.gaumina.lt' );
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Twitter\Event\TrendsReceivationFailed', $event );
            }
        }
    }
    
    public function testReceiveTrendsFailureAPIUnavailable()
    {
        $params = array(
            'priority' => 100,
            'title'    => 'test',
            'user_id'  => 123,
            'date'     => date('Y-m-d')
        );
        
        // Part one: enque the command
        $response = $this->handler->trends( $params['user_id'], $params['date'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'string', $response );
        
        // Part two: deque and execute the command
        $adapter = new Domain\Adapter\Twitter\TwitterAdapter();
        $adapter->_trends->setUri( 'http://www.unknownuri.lt' );
        $unitOfWork = $adapter->proceed();
        
        $this->assertNotNull( $unitOfWork );
        
        if ( !empty( $unitOfWork ) ) {
            
            foreach ( $unitOfWork->getEvents() as $event ) {
                
                $this->assertInstanceOf( 'Domain\Adapter\Twitter\Event\TrendsReceivationFailed', $event );
            }
        }
    }

	
    public function testCreateTweetCommandSuccess()
    {
        $params = array(
            'priority' => 100,
            'title'    => 'like a boss',
            'user_id'  => 123,
            'status'     => 'like a boss',
            'access_token' => '123',
            'access_token_secret' => '123',
            'long' => '123',
            'lat' => '123'
        );
        
        $response = $this->handler->tweet( $params['user_id'], $params['status'], $params['access_token'], $params['access_token_secret'], $params['long'], $params['lat'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'string', $response );
    }
    
    public function testCreateTweetCommandFailureInvalidData()
    {
        $params = array(
            'priority' => 100,
            'title'    => 'like a boss',
            'user_id'  => 123,
            'status'     => '',
            'access_token' => '',
            'access_token_secret' => '',
            'long' => '',
            'lat' => ''
        );
        
        $response = $this->handler->tweet( $params['user_id'], $params['status'], $params['access_token'], $params['access_token_secret'], $params['long'], $params['lat'], $params["priority"], $params["title"] );
        $this->assertInternalType( 'array', $response );
    }
    
    
    
    public function tearDown()
    {
        $db = Zend_Registry::get( 'db' );
        $db->query( 'TRUNCATE TABLE adapter_twitter_queue' );
        $db->query( 'TRUNCATE TABLE twitter_adapter' );
        $db->query( 'TRUNCATE TABLE twitter_adapter_data' );
        $db->query( 'TRUNCATE TABLE twitter_adapter_meta' );
        parent::tearDown();
    }
}