<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//$_SERVER['REQUEST_URI'] = str_replace( '/~tplanciunas/kompro', '', $_SERVER['REQUEST_URI'] );

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Configuration files
$configFiles = array(APPLICATION_PATH . '/configs/application.ini');

if (file_exists(APPLICATION_PATH . '/configs/local.ini')) {
    $configFiles[] = APPLICATION_PATH . '/configs/local.ini';
}

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        'config' => $configFiles
    )
);
$application->bootstrap()
            ->run();
