<?php

// Lib
use \MongoClient;
use \AMQPConnection;
use \AMQPChannel;
use \AMQPExchange;
use \AMQPQueue;


use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;

use ViewModel\User\User;


class IndexController extends BaseController {


    /**
     * @var Example
     */
    private $_exampleReadModel;

    public function init() {
        parent::init();
    }

    public function indexAction() {
	error_reporting(E_ALL);
        ini_set('display_errors', 'On');

	$this->view->a = 'test rabbitmq';

// NoSQL

        $str = 'test '.time();
        $m = new MongoClient();
        $db = $m->names;

        //insert
        $db->testnames->insert(array( 'name'=> $str));
        $collection = $db->testnames->findOne(array( 'name' => $str));
        $this->view->a = $collection['name'];
        
        //delete
        $db->testnames->remove(array( 'name'=> $str));

//	$user = $this->_getDiContainer()->userViewModel->save();
//	$this->view->b = User::findOne()->name;
		
// RabbitMQ

        /**
         * Filename: send.php
         * Purpose:
         * Send messages to RabbitMQ server using AMQP extension
         * Exchange Name: exchange1
         * Exchange Type: fanout
         * Queue Name: queue1
         */
        $connection = new AMQPConnection();
        $connection->connect();
        if (!$connection->isConnected()) {
            die('Not connected :(' . PHP_EOL);
        }
        // Open Channel
        $channel    = new AMQPChannel($connection);
        // Declare exchange
        $exchange   = new AMQPExchange($channel);
        $exchange->setName('exchange1');
        $exchange->setType('fanout');
        $exchange->declare();   
        // Create Queue
        $queue      = new AMQPQueue($channel);
        $queue->setName('queue1');
        $queue->declare();

        $message    = $exchange->publish('Custom Message (ts): '.time(), 'key1');
        if (!$message) {
            echo 'Message not sent', PHP_EOL;
        } else {
            echo 'Message sent!', PHP_EOL;
        }
        
        /**
         * Filename: receive.php
         * Purpose:
         * Receive messages from RabbitMQ server using AMQP extension
         * Exchange Name: exchange1
         * Exchange Type: fanout
         * Queue Name: queue1
         */
        $connection = new AMQPConnection();
        $connection->connect();
        if (!$connection->isConnected()) {
            die('Not connected :('. PHP_EOL);
        }
        // Open channel
        $channel    = new AMQPChannel($connection);
        // Open Queue and bind to exchange
        $queue      = new AMQPQueue($channel);
        $queue->setName('queue1');
        $queue->bind('exchange1', 'key1');
        $queue->declare();
        // Prevent message redelivery with AMQP_AUTOACK param
        while ($envelope = $queue->get(AMQP_AUTOACK)) {
            echo ($envelope->isRedelivery()) ? 'Redelivery' : 'New Message';
            echo PHP_EOL;
            echo $envelope->getBody(), PHP_EOL;
        }

    }

}
