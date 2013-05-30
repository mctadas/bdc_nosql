<?php

use \AMQPConnection;
use \AMQPChannel;
use \AMQPExchange;
use \AMQPQueue;


use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;

use ViewModel\User\User;
use ViewModel\Bill\Bill;

use \MongoBinData;

use ViewModel\Service\EventHandler\Updater;

class RabbitController extends BaseController {

    protected $_cluster = array('srvexa1', 'srvexa5', 'localhost');

    public function init() {
        parent::init();
        $this->connection = $this->getConnection();
    }
    
    public function indexAction() {
    	$this->sendmessageAction();
    }


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

    public function sendmessageAction() {

    	$messageText = array(
    			"type" => "order",
    			"data" => array(
    					"user_id" => $this->_getParam("user"),
    					"service" => $this->_getParam("service"),
    			),
    	);
    	
    	$message = $this->publishRabbitMessage('exchange1', 'service_queue', $messageText);
    			
        if (!$message) {
            echo 'Atsiprašome, įvyko klaida', PHP_EOL;
        } else {
            echo 'Vyksta paslaugos užsakymas', PHP_EOL;
        }
    }
    
    public function generatemessagesAction()
    {
    	$n_bills = 100;
    	 
    	$sql_con = mysql_connect('localhost', 'root', '');
    	mysql_select_db('mt');
    	$result = mysql_query('SELECT id FROM sql_bills LIMIT '.$n_bills, $sql_con);
    	while ($row = mysql_fetch_assoc($result)) {
    		$messageText = array("id" => $row['id']);
    		$this->publishRabbitMessage('exchange1', 'bills_queue', $messageText);
    	}
    	echo $n_bills.' žinučių sėkmingai sugeneruota.';
    }
    
    //FIXME make rabbit as a library and move function to BillsController
    public function handlebillsAction()
    {
    	$sql_con = mysql_connect('localhost', 'root', '');
    	mysql_select_db('mt');
    	
    	$bundle_size = 10;
    	for($i=1; $i<=$bundle_size; $i++)
    	{
    		$message = $this->getRabbitMessage('exchange1', 'bills_queue');
    		if(isset($message))
    		{
    			$this->store_bill($message->id, $sql_con);
    		} else {
    			sleep(1);	
    		}
    		
    	}
    }
    
    public function store_bill($sql_id, $sql_con)
    {
    	$result = mysql_query('SELECT * FROM sql_bills WHERE id='.$sql_id, $sql_con);
    	$row = mysql_fetch_assoc($result);
    	
	    $bill_document = array ( 'date' => date('Y F d', strtotime($row['date'])).' d.',
	    		'type' => $row['type'],
	    		'pdf_doc'  => new MongoBinData(file_get_contents("example.pdf"), 2),
	    		'has_doc' => true,
	    		'period' => $row['period'],
	    		'amount' => $row['amount'],
	    		//'pdf_report' => 'todo bin',
	    		'has_report' => false,
	    		'paid'   => $row['paid']);
	    $user = $this->_getDiContainer()->userViewModel->get_user($this->_user['username']);
	    $this->_getDiContainer()->billViewModel->save($bill_document, $user['key']);
	}
    
    public function listmessageAction() {
    }
    
    public function getRabbitMessage($channel_name, $queue_name){
        /**
         * Filename: receive.php
         * Purpose:
         * Receive messages from RabbitMQ server using AMQP extension
         * Exchange Name: exchange1
         * Exchange Type: fanout
         * Queue Name: queue1
         */
        
        // Open channel
        $channel    = new AMQPChannel($this->connection);
        // Open Queue and bind to exchange
        $queue      = new AMQPQueue($channel);
        $queue->setName($queue_name);
        $queue->bind($channel_name, 'key1');
        $queue->declare();
        
        // Prevent message redelivery with AMQP_AUTOACK param
//         while ($envelope = $queue->get(AMQP_AUTOACK)) {
        $envelope = $queue->get(AMQP_AUTOACK);
        if($envelope){
            //echo ($envelope->isRedelivery()) ? 'Redelivery' : 'New Message';
            //echo PHP_EOL;
            $message = $envelope->getBody();
            $message = json_decode($message);
            
            return $message;
        }

    }
    
    public function publishRabbitMessage($channel_name, $queue_name, $messageText)
    {
    	// RabbitMQ
    	
    	/**
    	 * Filename: send.php
    	 * Purpose:
    	 * Send messages to RabbitMQ server using AMQP extension
    	 * Exchange Name: exchange1
    	 * Exchange Type: fanout
    	 * Queue Name: queue1
    	 */
    	
    	// Open Channel
    	$channel    = new AMQPChannel($this->connection);
    	// Declare exchange
    	$exchange   = new AMQPExchange($channel);
    	$exchange->setName($channel_name);
    	$exchange->setType('fanout');
    	$exchange->declare();
    	// Create Queue
    	$queue      = new AMQPQueue($channel);
    	$queue->setName($queue_name);
    	$queue->declare();
    	
    	$message    = $exchange->publish( json_encode($messageText)  );
    }
    
    public function handle($event)
    {
    	switch ($event->type){
    		case 'order':
    			$this->_getDiContainer()->userViewModel
    				->addService($event->data->user_id, $event->data->service);
    			break;
    	}
    	
    }  
    
}
