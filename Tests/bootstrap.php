<?php
error_reporting( E_ALL | E_STRICT );
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) ) . "/../application");
set_include_path(get_include_path() . PATH_SEPARATOR .APPLICATION_PATH . '/../library');
set_include_path(get_include_path() . PATH_SEPARATOR .APPLICATION_PATH);
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__))); // the test dir

# ! IMPORTANT: this may vary depending on where you've got your Zend Framework installed...
set_include_path(get_include_path() . PATH_SEPARATOR .'/usr/share/php/libzend-framework-php');
date_default_timezone_set('UTC');

// instantiate the loader
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true); // we want the auto-loader to load ALL namespaces

?>
