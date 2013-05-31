<?php

use BDC\BaseController;
use BDC\Normalizer\Command\NormalizeData;
use BDC\DownloadManager;

use ViewModel\User\User;
use ViewModel\Session\Session;

// FIXME move to other file and correct import and namespaces
class Form_Login extends Zend_Form
{
    public function init()
    {
        $this->setName("login");
        $this->setMethod('post');
             
        $this->addElement('text', 'username', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('StringLength', false, array(0, 50)),
            ),
            'required'   => true,
            'label'      => 'Username:',
        ));

        $this->addElement('password', 'password', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(0, 50)),
            ),
            'required'   => true,
            'label'      => 'Password:',
        ));

        $this->addElement('submit', 'login', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Login',
        ));
    }
}

class AuthController extends BaseController
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $form = new Form_Login();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                if ($this->_process($form->getValues())) {
                    // We're authenticated! Redirect to the home page
                    
                    $this->redirectNext();
                }
            }
        }
        $this->view->form = $form;
        $this->setNext();
    }

    protected function setNext()
    {
        $next_action = $this->_getParam('na');
        if(isset($next_action))
        {
            $this->view->next = "/nc/".$this->_getParam('nc').'/na/'.$this->_getParam('na').'/info/'.$this->_getParam('info');
        }
    }

    protected function redirectNext($action = 'index', $controller = 'services')
    {
        $next = $this->_getParam('na');
        if(isset($next)){
            $this->_redirect($this->_getParam('nc').'/'.$this->_getParam('na').'/info/'.$this->_getParam('info'));  
                        
        } else {
            $this->_helper->redirector($action, $controller);
        }
    }

    protected function _process($values)
    {
        $username = $values['username'];
        $password = $values['password'];
        
        if ($this->_getDiContainer()->userViewModel->is_Valid($username, $password)) {
                $user = $this->_getDiContainer()->userViewModel->
                    get_user($username, $password);
                $this->_getDiContainer()->sessionViewModel->
                    save_session($username, Zend_Session::getId());
                return true;
            }
            return false;    
 
    }

    public function logoutAction()
    {
    	$this->_getDiContainer()->sessionViewModel->remove_session(Zend_Session::getId());
    	$this->_helper->redirector('index', 'index'); // back to main page
    }
    
    public function anonymousAction()
    {
    	$request = $this->getRequest();
    	if(isset($request) and $request->isPost())
    	{
    	    $post = $request->getPost();
    	    if($post['request'] == "Išsiųsti")
    	    {
    	      $post['service_click'] = $this->_getParam('info');
    	      $this->_getDiContainer()->requestViewModel->save($post);
    	      $this->redirectNext();
    	    }
    	}
    	$this->setNext();
    }

    public function listrequestsAction()
    {
        $requests = $this->_getDiContainer()->requestViewModel->getRequests();
        foreach($requests as $request)
        {
            print "<pre>";
            print_r($request);
            print "</pre>";
        }
    }
}





