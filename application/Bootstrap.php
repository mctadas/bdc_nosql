<?php

// Lib
use Bb4w\DiContainer;
use Bb4w\EventSourcing\SimpleEventPublisher;

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
			Zend_Registry::set('db', $db);

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

		// normalizer
		$c->normalizer = function() use ($c) {
					return new Bb4w\Normalizer\Normalizer($c->db, $c->dbNormalizer);
				};

		$c->downloadManager = function() use ($c) {
					return new Bb4w\DownloadManager($c->db, APPLICATION_PATH . '/exported');
				};

		// Adapter Queue
		$c->twitterQueue = function() use ($c) {
					return new Domain\Adapter\Twitter\TwitterQueue($c->db);
				};
		$c->facebookQueue = function() use ($c) {
					return new Domain\Adapter\Facebook\FacebookQueue($c->db);
				};
		$c->googlePlusQueue = function() use ($c) {
					return new Domain\Adapter\GooglePlus\GooglePlusQueue($c->db);
				};
		$c->mysqlQueue = function() use ($c) {
					return new Domain\Adapter\MySQL\MySQLQueue($c->db);
				};
		$c->oracleQueue = function() use ($c) {
					return new Domain\Adapter\Oracle\OracleQueue($c->db);
				};
		$c->postgresqlQueue = function() use ($c) {
					return new Domain\Adapter\PostgreSQL\PostgreSQLQueue($c->db);
				};
		$c->filescsvtxtQueue = function() use ($c) {
					return new Domain\Adapter\FilesCsvTxt\FilesCsvTxtQueue($c->db);
				};
		$c->filesxlsxQueue = function() use ($c) {
					return new Domain\Adapter\FilesXlsx\FilesXlsxQueue($c->db);
				};
		$c->soapQueue = function() use ($c) {
					return new Domain\Adapter\Soap\SoapQueue($c->db);
				};
		$c->pdfQueue = function() use ($c) {
					return new Domain\Adapter\PDF\PDFQueue($c->db);
				};
		$c->normalizedQueue = function() use ($c) {
					return new Domain\Adapter\Normalized\NormalizedQueue($c->db);
				};

		$c->adapterQueues = function() use ($c) {
					return array(
						$c->twitterQueue,
						$c->facebookQueue,
						$c->googlePlusQueue,
						$c->mysqlQueue,
						$c->oracleQueue,
						$c->postgresqlQueue,
						$c->filescsvtxtQueue,
						$c->filesxlsxQueue,
						$c->soapQueue,
						$c->pdfQueue,
						$c->normalizedQueue
					);
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
					return new Bb4w\EventSourcing\SimpleEventStore($c->db, $c->eventLogTableName);
				};
		$c->commandQueue = function() use ($c) {
					return new Bb4w\Domain\CommandQueue();
				};
		$c->commandDispatcher = function() use ($c) {
					return new Bb4w\Domain\CommandDispatcher(
									$c->commandQueue,
									$c->eventStore,
									$c->eventPublisher,
									$c->adapterQueues
					);
				};


		// Updaters
		$c->systemMonitorAdapterLogUpdater = function() use ($c) {
					return new \ViewModel\System\Monitor\EventHandler\AdapterLogUpdater($c->db);
				};
		$c->twitterUpdater = function () use ($c) {
					return new \ViewModel\Twitter\EventHandler\Updater($c->db);
				};
		$c->facebookUpdater = function () use ($c) {
					return new \ViewModel\Facebook\EventHandler\Updater($c->db);
				};
		$c->googlePlusUpdater = function () use ($c) {
					return new \ViewModel\GooglePlus\EventHandler\Updater($c->db);
				};
		$c->mysqlUpdater = function () use ($c) {
					return new \ViewModel\MySQL\EventHandler\Updater($c->db);
				};
		$c->oracleUpdater = function () use ($c) {
					return new \ViewModel\Oracle\EventHandler\Updater($c->db);
				};
		$c->postgresqlUpdater = function () use ($c) {
					return new \ViewModel\PostgreSQL\EventHandler\Updater($c->db);
				};
		$c->filescsvtxtUpdater = function () use ($c) {
					return new \ViewModel\FilesCsvTxt\EventHandler\Updater($c->db);
				};
		$c->filesxlsxUpdater = function () use ($c) {
					return new \ViewModel\FilesXlsx\EventHandler\Updater($c->db);
				};
		$c->soapUpdater = function () use ($c) {
					return new \ViewModel\Soap\EventHandler\Updater($c->db);
				};
		$c->pdfUpdater = function () use ($c) {
					return new \ViewModel\PDF\EventHandler\Updater($c->db);
				};
		$c->normalizedUpdater = function () use ($c) {
					return new \ViewModel\Normalized\EventHandler\Updater($c->db);
				};

		// View models
		$c->exampleViewModel = function() use ($c) {
					return new \ViewModel\Example\Example($c->db);
				};
		$c->systemMonitorAdapterLogViewModel = function() use ($c) {
					return new \ViewModel\System\Monitor\AdapterLog($c->db);
				};

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
