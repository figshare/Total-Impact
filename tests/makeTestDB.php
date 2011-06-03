<?php
/**
 * Creates database to do testing with.
 *
 * @author jason
 */
require_once 'bootstrap.php';

$dbContents = (isset($_GET['contents'])) ? $_GET['contents'] : 'full';



echo "deleting old test db...<br>";
print_r($couch->deleteDatabase());
echo "creating new test db...<br>";
print_r($couch->createDatabase());

$docs = json_decode(file_get_contents('./data/testDatabases/'.$dbContents.'.json'));

echo "storing docs from $dbContents...<br>";
print_r($couch->storeDocs($docs));

echo "storing design doc...<br>";

// make the design doc
$designDoc = new stdClass();
$designDoc->_id = "_design/main";
$designDoc->views = new stdClass();
$designDoc->views;

// get the view
$map = file_get_contents(APP_PATH . 'couchdb/views/to_update.js');
$view = new stdClass();
$view->map = $map;
$designDoc->views->to_update = $view;

print_r($couch->storeDoc($designDoc));


?>
