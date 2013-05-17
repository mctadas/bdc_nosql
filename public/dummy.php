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

/**
 * Drop database and import new SQL schema/data
 */

// Get DB connection with current ENV settings
$bootstrap = $application->getBootstrap();

/* @var $dbResource Zend_Application_Resource_Db */
$dbResource = $bootstrap->getPluginResource('db');
if ($dbResource) {
    echo 'DB connection initialized.<br />';
} else {
    throw new Zend_Exception('Cannot initialize DB connection.');
}

/* @var $db Zend_Db_Adapter_Pdo_Mysql */
$db = $dbResource->getDbAdapter();

// get database parameters
$dbConfig = $db->getConfig();
$dbName = $dbConfig['dbname'];

// Drop database if it exists
try {
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
} catch (Exception $ex) {
    // most likely database does not exist, so we do nothing
}
$db->exec("USE `" . $dbName . "`;");

// Import SQL dumps
$dumpPath = dirname(APPLICATION_PATH) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR; // path where dump files are located

$dumpFileNames = array(
    'dummy_data.sql'
);

foreach ($dumpFileNames as $fileName) {
    $content = file_get_contents($dumpPath . $fileName);
    $db->exec($content);
}
echo 'SQL dumps imported.<br />';

// Close connection to DB
$db->closeConnection();
echo '<br /><br />Done.';

