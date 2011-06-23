<?php
// This file must be run before unit tests

// define constants
defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__) ));
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . '/config/app.ini');
if (defined('ENV')){
    // do nothing
}
elseif (getenv('APPLICATION_ENV') == "production") {
     define('ENV', "production");
}
else {
     define('ENV', "development");
}

// set the include path so autoloading will get classes here:
set_include_path(get_include_path() . PATH_SEPARATOR .APP_PATH . '/library/' . PATH_SEPARATOR . APP_PATH);
date_default_timezone_set('UTC');

// instantiate the loader
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true); // we want the auto-loader to load ALL namespaces

?>
