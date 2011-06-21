<?php
/**
 * Creates a CouchDB database using the project's config file and the documents
 * contained in the Tests/data/couchDocs folder, then makes that available as
 * a PHP-on-couch object.
 */

class by_artifact_typeTest extends Tests_CouchDB_TestCase {


    protected $couch;

    function __construct() {
        $dbName =
        $couch = new Couch_Client($dsn, $dbname)



        $this->couch;
    }

    private function makeDB($name) {
        $config = new Zend_Config_Ini(CONFIG_PATH, ENV);
    }



}

?>
