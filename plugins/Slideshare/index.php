<?php
require_once 'FirePHPCore/fb.php';
FB::log("in plugin");
$pluginName = "plugin.py";
$input_query = $_GET["query"];
$input_escaped = escapeshellarg($input_query);
echo exec("python $pluginName $input_escaped");
?>
