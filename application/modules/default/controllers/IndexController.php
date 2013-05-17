<?php

// Lib
use Bb4w\BaseController;

use Bb4w\Normalizer\Command\NormalizeData;
use Bb4w\DownloadManager;

class IndexController extends BaseController
{
    /**
     * @var Example
     */
    private $_exampleReadModel;   
    
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
		$this->view->a = "aaaa";
//        die('test');
    }
    
	
}
