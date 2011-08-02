<?php
$pluginName = "plugin.py";
#$input = escapeshellarg(file_get_contents("php://input"));
$input = escapeshellarg($_REQUEST);
echo exec("python $pluginName $input");
?>
