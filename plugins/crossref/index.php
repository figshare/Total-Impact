<?php
$pluginName = "plugin.py";
$input = escapeshellarg(file_get_contents("php://input"));
echo exec("python $pluginName $input");
?>
