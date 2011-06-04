<?php
/**
 * Creates database to do testing with.
 *
 * @author jason
 */
require_once '../bootstrap.php';

$config = new Zend_Config_Ini(APP_PATH . '/config/app.ini', "production");
$couch = new Couch_Client($config->db->dsn, $config->db->name);
$dbContents = (isset($_GET['contents'])) ? $_GET['contents'] : 'full';



echo "deleting old test db...<br>";
//print_r($couch->deleteDatabase());
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

// get the show
$shows = new stdClass();
$shows->by_artifact_type = file_get_contents(APP_PATH . 'couchdb/views/to_update.js');
$designDoc->shows = $shows;

print_r($couch->storeDoc($designDoc));

// test the show
echo "<br>here is the show: <br>";
echo $couch->getShow('main', 'by_artifact_type', '1')


?>
