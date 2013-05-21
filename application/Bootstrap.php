<?php

// Lib
use BDC\DiContainer;
use BDC\EventSourcing\SimpleEventPublisher;

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap 
{

	protected function _initSession() 
	{
		Zend_Session::start();
	}

	protected function _initDb() 
	{
		$this->bootstrap('multidb');

		$dbResource = $this->getPluginResource('multidb');
		if ($dbResource) {
			$db = $dbResource->getDb('local');
			Zend_Registry::set('db', new \Shanty_Mongo_Document);

			$dbNormalizer = $dbResource->getDb('normalizer');
			Zend_Registry::set('db_normalizer', $dbNormalizer);
		} else {
			throw new Zend_Exception('Can not initialize DB connection');
		}
	}

	protected function _initFallbackAutoloader() 
	{
		$autoloader = Zend_Loader_Autoloader::getInstance();

		$autoloader->pushAutoloader(array($this, 'customAutoloader'), 'ViewModel\\');

		$autoloader->suppressNotFoundWarnings(true);
		$autoloader->setFallbackAutoloader(true);
	}

	public function customAutoloader($class) 
	{
		$namespaceParts = explode('\\', $class);

		$prefix = array_shift($namespaceParts);

		$prefixDirMap = array(
			"ViewModel" => 'view_models',
		);

		if (isset($prefixDirMap[$prefix])) {
			$directory = $prefixDirMap[$prefix];
		} else {
			trigger_error("Uknown prefix {$prefix}", E_USER_ERROR);
		}

		$fileName = implode('/', $namespaceParts) . '.php';

		Zend_Loader::loadFile($fileName, APPLICATION_PATH . "/{$directory}/", true);
	}

	protected function _initDiContainer() 
	{
		$c = new DiContainer();

		// Config
		$config = $this->getOptions();
		$c->config = function () use ($config) {
					return $config;
				};

		// DB
		$c->db = function () {
					return Zend_Registry::get('db');
				};
		$c->dbNormalizer = function () {
					return Zend_Registry::get('db_normalizer');
				};


		$c->downloadManager = function() use ($c) {
					return new BDC\DownloadManager($c->db, APPLICATION_PATH . '/exported');
				};


		// Event maps
		$c->domainEventRoutingMap = function() use ($c) {
					return new \Domain\EventRoutingMap($c);
				};

		// Event publisher
		$c->eventPublisher = function() use ($c) {
					$eventPublisher = new SimpleEventPublisher();

					$eventPublisher->registerEventHandlers($c->domainEventRoutingMap->getReportingDbUpdaters());

					return $eventPublisher;
				};

		// Domain
		$c->eventLogTableName = function () {
					return 'system_event_log';
				};
		$c->eventStore = function() use ($c) {
					return new BDC\EventSourcing\SimpleEventStore($c->db, $c->eventLogTableName);
				};
		$c->commandQueue = function() use ($c) {
					return new BDC\Domain\CommandQueue();
				};
//		$c->commandDispatcher = function() use ($c) {
//					return new BDC\Domain\CommandDispatcher(
//									$c->commandQueue,
//									$c->eventStore,
//									$c->eventPublisher,
//									$c->adapterQueues
//					);
//				};


		// Updaters
//		$c->systemMonitorAdapterLogUpdater = function() use ($c) {
//					return new \ViewModel\System\Monitor\EventHandler\AdapterLogUpdater($c->db);
//				};
//		$c->facebookUpdater = function () use ($c) {
//					return new \ViewModel\Facebook\EventHandler\Updater($c->db);
//				};

		 //View models
        $c->userViewModel = function() use ($c) {
                return new \ViewModel\User\User($c->db);
            };
                    
        $c->sessionViewModel = function() use ($c) {
                        return new \ViewModel\Session\Session($c->db);
                    };
                
		$c->exampleViewModel = function() use ($c) {
					return new \ViewModel\Example\Example($c->db);
				};
//		$c->systemMonitorAdapterLogViewModel = function() use ($c) {
//					return new \ViewModel\System\Monitor\AdapterLog($c->db);
//				};

		//Textile
		$c->textileRenderer = function() use ($c) {
					return new \Textile\Textile();
				};

		// Store DI container in Zend Registry
		Zend_Registry::set('di', $c);
	}

	protected function _initDoctype() 
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT');

		$view->headLink()->appendStylesheet('/styles/style.css');
	}

}
