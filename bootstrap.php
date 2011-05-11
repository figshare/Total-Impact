<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/library/');
require_once 'Zend/Loader/Autoloader.php';
require_once './config/configs.php';

// instantiate the loader
$loader = Zend_Loader_Autoloader::getInstance();

// we want the auto-loader to load ALL namespaces
$loader->setFallbackAutoloader(true);
$couch = new Couch_Client($configs['db']['dsn'], $configs['db']['db_name']);




?>
