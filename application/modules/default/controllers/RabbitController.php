<?php

use \AMQPConnection;
use \AMQPChannel;
use \AMQPExchange;
use \AMQPQueue;


use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;

use ViewModel\User\User;


class RabbitController extends BaseController {

    public function init() {
        parent::init();
    }
    
    public function indexAction() {
    	$this->sendmessageAction();
    }

<<<<<<< HEAD
=======

    public function getConnection()
    {
        shuffle($this->_cluster);
foreach($this->_cluster as $host) {        

            $connection = new AMQPConnection();
            $connection->setHost($host);
            try {
                $connection->connect();
            } catch ( Exception $e) { continue; }
        
            if (!$connection->isConnected()) {
            die('Not connected :(' . PHP_EOL);
            }
            return $connection;
        }
    }

>>>>>>> 894a67d... mistype fix
    public function sendmessageAction() {
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

        $message    = $exchange->publish( 'user:'.$this->_getParam('user').', '. $this->_getParam('service').' (ts): '.time(), 'key1');
        if (!$message) {
            echo 'Message not sent', PHP_EOL;
        } else {
            echo 'Message sent!', PHP_EOL;
        }
    }
    
    public function listmessageAction() {
    
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
            echo "<p></p>";
        }

    }

}
