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

        
        //$this->_getDiContainer()->contentViewModel->save(array('body' =>
        //		'<p> INTERNET Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse felis sapien, bibendum ut tincidunt non, pellentesque vitae massa. Cras arcu lectus, pulvinar sed felis eget, interdum sagittis urna. Quisque viverra ipsum lacus, vitae venenatis elit pretium a. Integer pellentesque id magna eget blandit. Nunc dictum, lacus a venenatis pellentesque, urna quam sodales lorem, vitae auctor leo nibh et purus. Aenean vitae posuere est. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nunc a nibh non elit pharetra pretium sit amet vitae ipsum. Vestibulum lacinia molestie sagittis. Nullam tempor ac diam in varius. Nam interdum fermentum diam, eu auctor tellus aliquet non. Curabitur vitae sodales arcu. Integer malesuada et est ac semper.</p>
        //		 <p> Vestibulum eget purus eget nibh convallis lobortis aliquam et tellus. Mauris et mi id nulla adipiscing sollicitudin. Aliquam at dapibus felis. Ut turpis urna, consequat nec hendrerit posuere, mollis </p>',
        //		'category' => 1,
        //       'uri' => '/services/internet',
        //		'date' => time(),
        //		));
        //$this->_getDiContainer()->contentViewModel->save(array('body' =>
       // 		'<p> TELEPHONE Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse felis sapien, bibendum ut tincidunt non, pellentesque vitae massa. Cras arcu lectus, pulvinar sed felis eget, interdum sagittis urna. Quisque viverra ipsum lacus, vitae venenatis elit pretium a. Integer pellentesque id magna eget blandit. Nunc dictum, lacus a venenatis pellentesque, urna quam sodales lorem, vitae auctor leo nibh et purus. Aenean vitae posuere est. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nunc a nibh non elit pharetra pretium sit amet vitae ipsum. Vestibulum lacinia molestie sagittis. Nullam tempor ac diam in varius. Nam interdum fermentum diam, eu auctor tellus aliquet non. Curabitur vitae sodales arcu. Integer malesuada et est ac semper.</p>
       // 		 <p> Vestibulum eget purus eget nibh convallis lobortis aliquam et tellus. Mauris et mi id nulla adipiscing sollicitudin. Aliquam at dapibus felis. Ut turpis urna, consequat nec hendrerit posuere, mollis </p>',
        //		'category' => 0,
        //        'uri' => '/services/telephone',
       // 		'date' => time(),
        //));
        
        //____________________
        
        $user = $this->_getDiContainer()->userViewModel->get_user('a','a');
        
        // create user if it doesn't exist   
        if (empty($user))
        {
            $this->_getDiContainer()->userViewModel->create_user('a','a', '12345', 0);
            $this->_getDiContainer()->userViewModel->create_user('b','b', '12346', 1);
            $user = $this->_getDiContainer()->userViewModel->get_user('a','a');
            $this->craeteusers();
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

    public function craeteusers()
    {
    	for($i=1; $i<=300000; $i++){
    		$this->_getDiContainer()->userViewModel->create_user('u'.sprintf('%08d',$i),
    															 'u'.sprintf('%08d',$i),
    				                                             $i);
    	}
    }
}
