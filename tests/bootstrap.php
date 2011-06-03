<?php
error_reporting( E_ALL | E_STRICT );
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__) ) . "/../");
set_include_path(get_include_path() . PATH_SEPARATOR .APP_PATH . '/library/');
date_default_timezone_set('UTC');

// instantiate the loader
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true); // we want the auto-loader to load ALL namespaces

// set config and database globals
$config = new Zend_Config_Ini(APP_PATH . '/config/app.ini', "production");
$couch =  new Couch_Client($config->db->dsn, $config->db->viewTester->name);

?>

