<?php

// Lib
use Bb4w\BaseController;

class System_MonitorController extends BaseController
{
    public function init()
    {
        parent::init();        
    }

    public function logAction()
    {
        $foo = $this->_getDiContainer()->systemMonitorAdapterLogViewModel->getAdapterLogList();
        
        $this->view->adapterLogList = $foo;
    }
    
    
}