<?php

// Lib
use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;
use \MongoClient;

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
		$this->view->a = "aaaa";
		
		$m = new MongoClient();
		$db = $m->names;
		$collection = $db->testnames->findOne();
		$this->view->a = $collection['name'];
	      

    }

}
