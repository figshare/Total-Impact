<?php
/**
 * Updates a design doc in the database
 *
 * @author jason
 */
require_once '../bootstrap.php';

$docName = "main";
$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
$couch = new Couch_Client($config->db->dsn, $config->db->name);
$dbContents = (isset($_GET['contents'])) ? $_GET['contents'] : 'full';

$designDoc = $couch->getDoc('_design/'.$docName);

echo "storing design doc...<br>";

// get the view
$map = file_get_contents(APP_PATH . 'couchdb/views/to_update.js');
$view = new stdClass();
$view->map = $map;
$designDoc->views->to_update = $view;

// get the show
$shows = new stdClass();
$shows->by_artifact_type = file_get_contents(APP_PATH . 'couchdb/views/to_update.js');
$designDoc->shows = $shows;

// upload the doc
$couch->storeDoc($designDoc);

// test the show
echo "<br>here is the show: <br>";
echo $couch->getShow('main', 'by_artifact_type', '1')


?>
