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
        
        $this->_getDiContainer()->serviceViewModel->removeServices();
            $this->_getDiContainer()->serviceViewModel->createService(array( 'type'     => 'internet',
                                                                             'label'    => 'Šviesolaidinis internetas',
                                                                             'services' => array(
                                                                                                array('id'    => '01',
                                                                                                      'label' => 'Para šviesolaidis',
                                                                                                      'price' => '9,90 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('id'    => '02',
                                                                                                      'label' => 'Bazinis šviesolaidis',
                                                                                                      'price' => '29,90 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('id'    => '03',
                                                                                                      'label' => 'Optimalus šviesolaidis',
                                                                                                      'price' => '39,91 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('id'    => '04',
                                                                                                      'label' => 'Premium šviesolaidis',
                                                                                                      'price' => '69,90 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                                                                                )));
                                                                                                
                                                                                                
            $this->_getDiContainer()->serviceViewModel->createService(array( 'type'    => 'phone',
                                                                             'label'    => 'Pagrindiniai pokalbių planai ',
                                                                             'services' => array(
                                                                                                array('id'    => '11',
                                                                                                      'label' => '„Neribotas plius“',
                                                                                                      'price' => '36,90 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('id'    => '12',
                                                                                                      'label' => '„Šalyje plius“',
                                                                                                      'price' => '14,00 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('id'    => '13',
                                                                                                      'label' => '„Mobilusis“',
                                                                                                      'price' => '29,90 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('id'    => '14',
                                                                                                      'label' => '„Pasaulis plius“',
                                                                                                      'price' => '29,90 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('id'    => '15',
                                                                                                      'label' => 'Bazinis plius',
                                                                                                      'price' => '23,00 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                                                                                )));
            $this->_getDiContainer()->serviceViewModel->createService(array( 'type'    => 'tv',
                                                                             'label'    => 'Televizija',
                                                                             'services' => array(
                                                                                                array('id'    => '21',
                                                                                                      'label' => 'Televizija „Interaktyvioji GALA“',
                                                                                                      'price' => '29,90 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                                                                                array('id'    => '22',
                                                                                                      'label' => 'Televizija „Skaitmeninė GALA“',
                                                                                                      'price' => '19,90 Lt/mėn. ',
                                                                                                      'desc'  => 'description'),
                                              
                                                                                                )));
        
        
    }       
}
