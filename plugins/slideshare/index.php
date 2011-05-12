<?php
include '../common/RestServer.php';
include '../common/Metric.php';
include '../common/MetricList.php';
include 'SlidesharePluginController.php';

spl_autoload_register(); // don't load our classes unless we use them

$mode = 'debug'; // 'debug' or 'production'
$server = new RestServer($mode);
// $server->refreshCache(); // uncomment momentarily to clear the cache if classes change in production mode
$server->addClass('SlidesharePluginController');
$server->handle();
?>
