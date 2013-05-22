<?php

// Lib

use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;

use ViewModel\User\User;
use ViewModel\Session\Session;


class OtherController extends BaseController {


    public function init() {
        parent::init();
        
        //FIXME: make it reusable one line call
        $this->_user = $this->_getDiContainer()->sessionViewModel->
            get_session(Zend_Session::getId());
        if (empty($this->_user))
        {
            $this->_helper->redirector('index', 'auth');
        }        
       
    }

    public function indexAction() {
        $this->view->username = $this->_user['username'];
    }
}
