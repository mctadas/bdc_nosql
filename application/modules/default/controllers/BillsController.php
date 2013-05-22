<?php

use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;

use ViewModel\Bill\Bill;
use ViewModel\User\User;

class BillsController extends BaseController {


    /**
     * @var Example
     */
    private $_exampleReadModel;

    public function init() {
        parent::init();
        
        $this->_user = $this->_getDiContainer()->sessionViewModel->
            get_session(Zend_Session::getId());
        if (empty($this->_user))
        {
            $this->_helper->redirector('index', 'auth');
        }
    }

    public function indexAction() {       
    }       
    
    public function historyAction() {       
        $user = $this->_getDiContainer()->userViewModel->get_user($this->_user['username']);   
        $this->view->bills = $this->_getDiContainer()->billViewModel->get_bills($user['key']);
    }       
    
    public function manageAction() {  
        $bill_document = array ( 'date' => date('Y F d').' d.',
                                 'type' => 'Saskaita',
                                 'doc'  => 'todo bin',
                                 'period' => '2012 birzelis',
                                 'amount' => '29,90 Lt',
                                 'report' => 'todo bin',
                                 'paid'   => false);
        $user = $this->_getDiContainer()->userViewModel->get_user($this->_user['username']);   
        $this->_getDiContainer()->billViewModel->save($bill_document, $user['key']);
        
        // add one antry to db and rediredt to history page
        $this->_helper->redirector('history', 'bills');
    }       
}
