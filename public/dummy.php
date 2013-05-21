<?php
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

// create bummy user;
//$this->_getDiContainer()->userViewModel->create_dummie('a','a','12345');
echo '<br /><br />Done.';

