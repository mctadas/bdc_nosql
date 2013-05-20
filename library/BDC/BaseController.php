<?php

namespace BDC;

use \BDC\Session;
// Zend
use \Zend_Controller_Action;
use \Zend_Registry;
use \Zend_Session_Namespace;

/**
 * @package Kompro 
 */
abstract class BaseController extends Zend_Controller_Action 
{

	/**
	 * @var Zend_Session_Namespace
	 */
	protected $_session;

	public function init() 
	{
		parent::init();

		// create session namespace as "/module/controller/action/"
		$sessionNamespace = '/' . $this->_getParam("module") . '/' . $this->_getParam("controller") . '/' . $this->_getParam("action") . '/';
		$c = $this->_getDiContainer();
		$c->session = function () use ($sessionNamespace) {
					return new Session($sessionNamespace);
				};
		$this->_session = $c->session;

		$this->view->addScriptPath(APPLICATION_PATH . "/layouts/scripts/");
	}

	/**
	 * @return DiContainer
	 */
	protected function _getDiContainer() 
	{
		return Zend_Registry::get('di');
	}

	/**
	 * @return \Zend_Controller_Action_Helper_Redirector
	 */
	protected function _getRedirectorHelper() 
	{
		return $this->_helper->getHelper('Redirector');
	}

	/**
	 * @return \Zend_Controller_Action_Helper_Url
	 */
	protected function _getUrlHelper() 
	{
		return $this->_helper->getHelper('Url');
	}

	/**
	 * @return \Bb4w_Controller_Action_Helper_FlashMessenger
	 */
	protected function _getFlashMessenger() 
	{
		return $this->_helper->getHelper('FlashMessenger');
	}

	protected function _disableLayout() 
	{
		$this->_helper->layout->disableLayout();
	}

	protected function _disableRender() 
	{
		$this->_helper->viewRenderer->setNoRender(true);
	}

	protected function handleRequest(Zend\Rest\Server $server, $requestData = false) 
	{
		$responseType = $this->getRequest()->getParam('responseType');
		$responseType = empty($responseType) ? 'xml' : strtolower($responseType);

		switch ($responseType) {

			case 'json':

				$response = $server->handle($requestData, true);

				if (is_scalar($response)) {

					$response = array('success' => $response);
				} elseif ($response instanceof \DOMDocument) {

					$response = array('failure' => $response->textContent);
				}

				echo json_encode($response);

				break;

			case 'xml':
			default:

				$response = $server->handle($requestData);

				header('Content-Type: text/xml');
				echo $response;
		}
	}

}