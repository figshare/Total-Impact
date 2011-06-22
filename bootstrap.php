<?php
/**
 * This file must be run before unit tests
 */


// just here for development
error_reporting( E_ALL | E_STRICT );
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__) ));
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . '/config/app.ini');
if (defined(ENV)){
    // do nothing
}
elseif (getenv('APPLICATION_ENV') == "production") {
     define('ENV', "production");
}
else {
     define('ENV', "development");
}

set_include_path(get_include_path() . PATH_SEPARATOR .APP_PATH . '/library/' . PATH_SEPARATOR . APP_PATH);
date_default_timezone_set('UTC');

// instantiate the loader
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true); // we want the auto-loader to load ALL namespaces

?>
