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
        
        $this->view->a = 'user:'.$user['username']." password:".$user['password'];
        
        // create services if they do not exist
        $service = $this->_getDiContainer()->serviceViewModel->getRandomService();
        
        if(empty($service)){
            $this->_getDiContainer()->serviceViewModel->createService(array( 'type'     => 'internet',
                                                                             'label'    => 'Sviesolaidinis internetas',
                                                                             'services' => array(
                                                                                                array('label' => 'Para sviesolaidis',
                                                                                                      'price' => '9,90 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('label' => 'Bazinis sviesolaidis',
                                                                                                      'price' => '29,90 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('label' => 'Optimalus sviesolaidis',
                                                                                                      'price' => '39,91 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('label' => 'Premium sviesolaidis',
                                                                                                      'price' => '69,90 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                                                                                )));
                                                                                                
                                                                                                
            $this->_getDiContainer()->serviceViewModel->createService(array( 'type'    => 'phone',
                                                                             'label'    => 'Pagrindiniai pokalbiu planai ',
                                                                             'services' => array(
                                                                                                array('label' => '„Neribotas plius“',
                                                                                                      'price' => '36,90 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('label' => '„Salyje plius“',
                                                                                                      'price' => '14,00 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('label' => '„Mobilusis“',
                                                                                                      'price' => '29,90 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('label' => '„Pasaulis plius“',
                                                                                                      'price' => '29,90 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('label' => 'Bazinis plius',
                                                                                                      'price' => '23,00 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                                                                                )));
            $this->_getDiContainer()->serviceViewModel->createService(array( 'type'    => 'tv',
                                                                             'label'    => 'Televizija',
                                                                             'services' => array(
                                                                                                array('label' => 'Televizija „Interaktyvioji GALA“',
                                                                                                      'price' => '29,90 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('label' => 'Televizija „Skaitmenine GALA“',
                                                                                                      'price' => '19,90 Lt/men. ',
                                                                                                      'desc'  => 'description'),
                                              
                                                                                                )));
        }
        
    }       
}
