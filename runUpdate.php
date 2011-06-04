<?php
/* 
 * This script runs an update with a given plugin. It updates all the collections
 * in the database that haven't yet been successfully updated by that plugin.
 *
 * Include the plugin name as the value of the "source" parameter, like this:
 *    GET http://baseurl.tld/runUpdate.php?source=<sourceName>
 */

require_once 'bootstrap.php';
$updater = UpdaterFactory::makeUpdater($_GET['source']);
$updater->update();



?>
