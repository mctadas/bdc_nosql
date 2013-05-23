<?php

use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;

use ViewModel\Bill\Bill;
use ViewModel\User\User;

use \MongoBinData;

class OrdersController extends BaseController {


    /**
     * @var Example
     */
    private $_exampleReadModel;

    public function init() {
        parent::init();
    }
    
    public function serviceAction()
    {
            $this->view->bill = $this->_getDiContainer()->billViewModel->get_random_bill();
        
    }
}
