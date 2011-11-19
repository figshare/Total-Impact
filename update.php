<?php
ob_implicit_flush(TRUE);
require_once './bootstrap.php';
#require_once 'FirePHPCore/fb.php';
// TRUE = disable all output buffering, 
// and automatically flush() 
// immediately after every print or echo 

function sanitize($str, $alphaNumOnly=TRUE){
    $newStr = "";
    if ($alphaNumOnly) {
        $newStr = preg_replace('/[^A-Za-z0-9]/', '', $str);
    }
    else {
        $newStr = strip_tags($str);
    }
    return $newStr;
}

$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
$collection = new Models_Collection();


if (isset($_REQUEST['id'])) {
    $collectionId = $_REQUEST['id'];
} else {
    if (isset($_REQUEST['quickreport'])) {
        $seed = new Models_Seeder();
        if (isset($_REQUEST['mendeleygroup'])) {
            $artifactIdList = $seed->getMendeleyGroupArtifacts(sanitize($_REQUEST['mendeleygroup']));
            $artifactIds = implode("\n", $artifactIdList); # \n has to be in DOUBLE quotes not single quotes
        } elseif (isset($_REQUEST['mendeleyprofile'])) {
            $artifactIdList = $seed->getMendeleyProfileArtifacts(sanitize($_REQUEST['mendeleyprofile']));
            $artifactIds = implode("\n", $artifactIdList); # \n has to be in DOUBLE quotes not single quotes
        }
    } else {
        $artifactIds = sanitize($_REQUEST['list']);
    }

    if (isset($_REQUEST['name'])) {
        sanitize($title = $_REQUEST['name']);
    } else {
        $title = "";
    }

    // save the new collection
    $storedDoc = $collection->create($title, $artifactIds, $config);
    $collectionId = $storedDoc->id;
}
error_log("now update");
// get the updates
$collection->update($collectionId, $config);
// redirect to the report page for this plugin
header("HTTP/1.1 200 OK");
echo $collectionId;
?>
