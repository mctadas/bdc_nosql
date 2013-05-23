<?php

use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;


class ServicesController extends BaseController {


    /**
     * @var Example
     */
    private $_exampleReadModel;

    public function init() {
        parent::init();
    }

    public function indexAction() {
    $this->view->services = $this->_getDiContainer()->serviceViewModel->getServices();
    }     
    
    public function internetAction() {
        $this->view->services = $this->_getDiContainer()->serviceViewModel->getServices('internet');
    }
    
    public function telephoneAction() {
        $this->view->services = $this->_getDiContainer()->serviceViewModel->getServices('phone');
    }
    
    public function televisionAction() {
       $this->view->services = $this->_getDiContainer()->serviceViewModel->getServices('tv');
    }
}
