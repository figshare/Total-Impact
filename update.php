<?php

require_once './bootstrap.php';
#require_once 'FirePHPCore/fb.php';
// TRUE = disable all output buffering, 
// and automatically flush() 
// immediately after every print or echo 
ob_implicit_flush(TRUE);


$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
$collection = new Models_Collection();


if (isset($_REQUEST['id'])) {
    $collectionId = $_REQUEST['id'];
} else {
    if (isset($_REQUEST['quickreport'])) {
        $seed = new Models_Seeder();
        if (isset($_REQUEST['mendeleygroup'])) {
            $artifactIdList = $seed->getMendeleyGroupArtifacts($_REQUEST['mendeleygroup']);
            $artifactIds = implode("\n", $artifactIdList); # \n has to be in DOUBLE quotes not single quotes
        } elseif (isset($_REQUEST['mendeleyprofile'])) {
            $artifactIdList = $seed->getMendeleyProfileArtifacts($_REQUEST['mendeleyprofile']);
            $artifactIds = implode("\n", $artifactIdList); # \n has to be in DOUBLE quotes not single quotes
        }
    } else {
        $artifactIds = $_REQUEST['list'];
    }

    if (isset($_REQUEST['name'])) {
        $title = $_REQUEST['name'];
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
echo "<script>location.href='./report.php?id=$collectionId'</script>";
?>
