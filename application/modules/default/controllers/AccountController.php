<?php

use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;

use ViewModel\Session\Session;

class AccountController extends BaseController {

    public function init() {
        $this->_restricted = true;
        parent::init();
        
        
        
     
    }

    public function indexAction() {
    }       
}
