<?php

// Lib
use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;

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
    }

}
