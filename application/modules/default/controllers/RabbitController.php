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
    protected $_cluster = array(
            'srvexa1',
            'srvexa5',
            'localhost'
    );

    public function init()
    {
        parent::init();
        $this->connection = $this->getConnection();
        
        $this->sql_con = mysql_connect('localhost', 'root', '');
        mysql_select_db('mt');
    }

    public function indexAction()
    {
        $this->sendmessageAction();
    }

    public function getConnection()
    {
        shuffle($this->_cluster);
        foreach ( $this->_cluster as $host )
        {
            $connection = new AMQPConnection();
            $connection->setHost($host);
            try
            {
                $connection->connect();
            } catch ( Exception $e )
            {
                continue;
            }
            
            if (! $connection->isConnected())
            {
                die('Not connected :(' . PHP_EOL);
            }
            return $connection;
        }
    }

    public function sendmessageAction()
    {
        $messageText = array(
                "type" => "service",
                "data" => array(
                        "user_id" => $this->_getParam("user"),
                        "service" => $this->_getParam("service")
                )
        );
        
        $message = $this->publishRabbitMessage('exchange1', 'service_queue', $messageText);
        
        if (! $message)
        {
            echo 'Atsiprašome, įvyko klaida', PHP_EOL;
        } else
        {
            echo 'Vyksta paslaugos užsakymas', PHP_EOL;
        }
    }

    public function generatemessagesAction()
    {
        $n_bills = 1;
        
        $sql_con = $this->sql_con;
        $result = mysql_query('SELECT id FROM sql_bills LIMIT ' . $n_bills, $sql_con);
        while ( $row = mysql_fetch_assoc($result) )
        {
            $messageText = $this->generate_json_bill($row);
            $this->publishRabbitMessage('exchange1', 'bills', $messageText);
        }
        echo $n_bills . ' žinučių sėkmingai sugeneruota.';
    }

    public function generate_json_bills($row)
    {
        $user = $this->_getDiContainer()->userViewModel->get_user($row['uid']);
    
        $bill_document = array(
                'date' => date('Y F d', strtotime($row['date'])) . ' d.',
                'type' => $row['type'],
                'pdf_doc' => new MongoBinData(file_get_contents("example.pdf"), 2),
                'has_doc' => true,
                'period' => $row['period'],
                'amount' => $row['amount'],
                'has_report' => false,
                'paid' => $row['paid'],
                'ukey' => $user['key']
        );
        return $bill_document;
    
    }
    
    public function handlebilleventsAction() // FIXME make rabbit a a library
                                             // and move function to
                                             // BillsController
    {
        date_default_timezone_set('Europe/Vilnius');
        
        $current_time = date('i');
        while ( $current_time == date('i') )
        {
            $event = $this->getRabbitMessage('exchange1', 'bills_queue');
            if (isset($event))
            {
                $this->handle($event);
            } else
            {
                sleep(1);
            }
        }
    }

    public function handleserviceeventsAction()
    {
        date_default_timezone_set('Europe/Vilnius');
        
        $current_time = date('i');
        while ( $current_time == date('i') )
        {
            $event = $this->getRabbitMessage('exchange1', 'service_queue');
            if (isset($event))
            {
                $this->handle($event);
            } else
            {
                sleep(1);
            }
        }
    }

    public function handle($event)
    {
        switch ($event->type) {
            case 'bill' :
                $this->store_bill($event->data->id, $this->sql_con);
                break;
            case 'service' :
                $this->_getDiContainer()->userViewModel->addService($event->data->user_id, $event->data->service);
                break;
        }
    }

    

    public function store_bill($sql_id, $sql_con)
    {
        $result = mysql_query('SELECT * FROM sql_bills WHERE id=' . $sql_id, $sql_con);
        $row = mysql_fetch_assoc($result);
        
        $bill_document = array(
                'date' => date('Y F d', strtotime($row['date'])) . ' d.',
                'type' => $row['type'],
                'pdf_doc' => new MongoBinData(file_get_contents("example.pdf"), 2),
                'has_doc' => true,
                'period' => $row['period'],
                'amount' => $row['amount'],
                // 'pdf_report' => 'todo bin',
                'has_report' => false,
                'paid' => $row['paid']
        );
        $user = $this->_getDiContainer()->userViewModel->get_user($row['uid']);
        $this->_getDiContainer()->billViewModel->save($bill_document, $user['key']);
    }

    public function getRabbitMessage($channel_name, $queue_name)
    {
        try
        {
            $channel = new AMQPChannel($this->connection);
            
            $queue = new AMQPQueue($channel);
            $queue->setName($queue_name);
            // $queue->declare();
            $queue->bind($channel_name, $queue_name);
            // $queue->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
            // $queue->declare();
            
            // Prevent message redelivery with AMQP_AUTOACK param
            // while ($envelope = $queue->get(AMQP_AUTOACK)) {
            $envelope = $queue->get(AMQP_AUTOACK);
            if ($envelope)
            {
                // echo ($envelope->isRedelivery()) ? 'Redelivery' : 'New
                // Message';
                // echo PHP_EOL;
                $message = $envelope->getBody();
                $message = json_decode($message);
                
                return $message;
            }
        } catch ( Exception $e )
        {
            print_r($e);
            die();
        }
    }

    public function publishRabbitMessage($channel_name, $queue_name, $messageText)
    {
        try
        {
            $channel = new AMQPChannel($this->connection);
            
            $exchange = new AMQPExchange($channel);
            $exchange->setName($channel_name);
            $exchange->setType(AMQP_EX_TYPE_DIRECT);
            $exchange->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
            $exchange->declare();
            
            // Create Queue
            $queue = new AMQPQueue($channel);
            $queue->setName($queue_name);
            $queue->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
            $queue->declare();
            
            $queue->bind($channel_name, $queue_name);
            
            $message = $exchange->publish(json_encode($messageText), $queue_name);
            return $message;
        } catch ( Exception $e )
        {
            print_r($e);
            die();
        }
    }
}
