<?php

// Lib
use \MongoClient;

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
		
        $str = 'test '.time();
        $m = new MongoClient();
        $db = $m->names;

        //insert
        $db->testnames->insert(array( 'name'=> $str));
        $collection = $db->testnames->findOne(array( 'name' => $str));
        $this->view->a = $collection['name'];
        
        //delete
        $db->testnames->remove(array( 'name'=> $str));
        $collection = $db->testnames->findOne(array( 'name' => $str));
        $this->view->b = $collection['name'];
        

  		$user = $this->_getDiContainer()->userViewModel->save();
  		$this->view->b = User::findOne()->name;
		
    }

}
