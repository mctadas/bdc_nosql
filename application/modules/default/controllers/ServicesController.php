<?php

use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;
class ServicesController extends BaseController {
    
    /**
     *
     * @var Example
     */
    private $_exampleReadModel;

    public function init()
    {
        parent::init();
        $this->view->next_action = $this->getRequest()->getActionName();
        $this->view->user_services = $this->_getDiContainer()->userViewModel->getUserSercives($this->_user['username']);
        $this->view->content = $this->_getDiContainer()->contentViewModel->get_by_uri('services/' . $this->view->next_action);
    }

    public function indexAction()
    {
        $this->view->services = $this->_getDiContainer()->serviceViewModel->getServices();
    }

    public function internetAction()
    {
        $this->view->services = $this->_getDiContainer()->serviceViewModel->getServices('internet');
    }

    public function telephoneAction()
    {
        $this->view->services = $this->_getDiContainer()->serviceViewModel->getServices('phone');
    }

    public function televisionAction()
    {
        $this->view->services = $this->_getDiContainer()->serviceViewModel->getServices('tv');
    }

    public function editAction()
    {
        $request = $this->getRequest();
        $values = $request->getPost();
        $this->view->content = $this->_getDiContainer()->contentViewModel->update($values['mce_0'], $values['uri'], $values['category']);
    }
}
