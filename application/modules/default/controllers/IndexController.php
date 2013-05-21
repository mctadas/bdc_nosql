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

// Create dummy user u:a, p:a

        $user = $this->_getDiContainer()->userViewModel->get_user('a','a');
        
        // create user if it doesn't exist   
        if (empty($user))
        {
            $this->_getDiContainer()->userViewModel->create_user('a','a','12345');
            $user = $this->_getDiContainer()->userViewModel->get_user('a','a');
        }
        
        $this->view->a = 'user:'.$user['username']." key:".$user['key'];
     
        
    }       
}
