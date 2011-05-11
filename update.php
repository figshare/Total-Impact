<?php

require_once './bootstrap.php';
$updater = new Updater($couch, new Zend_Http_Client, $configs);
$updater->update();

?>
