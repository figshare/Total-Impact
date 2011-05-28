<?php
$pluginName = "plugin.py";
$idsJsonStr = escapeshellarg((string)$_POST);
echo exec('python ' . $pluginName . ' ' . $idsJsonStr);


?>
