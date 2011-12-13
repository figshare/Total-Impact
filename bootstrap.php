<?php
// This file must be run before unit tests

// define constants
defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__) ));
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . '/config/app.ini');
defined('CREDS_PATH') || define('CREDS_PATH', APP_PATH . '/config/creds.ini');
if (defined('ENV')){
    // do nothing
}
elseif (getenv('APPLICATION_ENV') == "production") {
     define('ENV', "production");
}
else {
     define('ENV', "development");
}



# user levels are E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE
function breadcrumb($msg, $level=E_USER_NOTICE) {
	trigger_error($msg, E_USER_NOTICE);
}

////// This code from http://www.php.net/manual/en/errorfunc.examples.php
// we will do our own error handling
error_reporting(0);

// user defined error handling function
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars) 
{
    // timestamp for the error entry
    $dt = date("Y-m-d H:i:s (T)");

    // define an assoc array of error string
    // in reality the only entries we should
    // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
    // E_USER_WARNING and E_USER_NOTICE
    $errortype = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                );
    // set of errors for which a var trace will be saved
    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
    
    $err = $_SERVER['REMOTE_ADDR'] . "; ";
	#$pathinfo = $_SERVER["SCRIPT_FILENAME"];
	#$pathinfo = pathinfo($_SERVER["SCRIPT_FILENAME"]);
    #$err .= $filename["filename"] . ".php line " . $linenum . ". ";
	$pathinfo = pathinfo($filename);
    $err .= $pathinfo["basename"] . " line " . $linenum . ". ";

	
	if ($errno != 1024) {
	    $err .= "errornum:>" . $errno . "; ";
	    $err .= "errortype>" . $errortype[$errno] . "; ";
	    if (in_array($errno, $user_errors)) {
	        $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>";
	    }
	}

    $err .= "(" . $errortype[$errno] . ") ";
    $err .= $errmsg . "; ";
    
    // for testing
    // echo $err;

    // save to the error log
    error_log($err, 0);
}

$old_error_handler = set_error_handler("userErrorHandler");

// set the include path so autoloading will get classes here:
set_include_path(get_include_path() . PATH_SEPARATOR .APP_PATH . '/library/' . PATH_SEPARATOR . APP_PATH);
date_default_timezone_set('UTC');

// instantiate the loader
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();

$loader->setFallbackAutoloader(true); // we want the auto-loader to load ALL namespaces


?>
