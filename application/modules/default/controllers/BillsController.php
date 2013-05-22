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


class BillsController extends BaseController {


    /**
     * @var Example
     */
    private $_exampleReadModel;

    public function init() {
        parent::init();
    }

    public function indexAction() {       
    }       
    
    public function historyAction() {       
    }       
    
    public function manageAction() {       
    }       
}
