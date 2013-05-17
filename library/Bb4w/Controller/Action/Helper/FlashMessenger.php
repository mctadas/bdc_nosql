<?php

class Bb4w_Controller_Action_Helper_FlashMessenger extends Zend_Controller_Action_Helper_FlashMessenger
{
    protected $_module;
    protected $_controller;
    protected $_action;

    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const OK = 'ok';

    public function  __construct() {
        parent::__construct();

        $this->_module     = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        $this->_controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $this->_action     = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    }
    
    private function _addMessage($message, $type = null, $args = FALSE)
    {
        if (!empty($args)) {
            $message = vsprintf($message, $args);
        }
        return parent::addMessage(array($type, $message));
    }

    public function setInfo($message, $args = FALSE)
    {
        return $this->_addMessage($message, self::INFO, $args);
    }

    public function setOk($message, $args = FALSE)
    {
        return $this->_addMessage($message, self::OK, $args);
    }

    public function setWarning($message, $args = FALSE)
    {
        return $this->_addMessage($message, self::WARNING, $args);
    }

    public function setError($message, $args = FALSE)
    {
        return $this->_addMessage($message, self::ERROR, $args);
    }

    public function setDirect($message, $type = null, $args = FALSE)
    {
        return $this->_addMessage($message, $type, $args);
    }

    public function setErrors(array $messages)
    {
        foreach($messages as $v) {
            $this->setError($v);
        }
    }
    public function setOks(array $messages)
    {
        foreach($messages as $v) {
            $this->setOk($v);
        }
    }

    public function setInfos(array $messages)
    {
        foreach($messages as $v) {
            $this->setInfo($v);
        }
    }

    public function setWarnings(array $messages)
    {
        foreach($messages as $v) {
            $this->setWarning($v);
        }
    }

}