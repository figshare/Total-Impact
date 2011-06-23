<?php
/**
 * Creates a CouchDB database using the project's config file and the documents
 * contained in the Tests/data/couchDocs folder, then makes that available as
 * a PHP-on-couch object.
 */

class Tests_CouchDB_TestCase extends PHPUnit_Framework_TestCase {

    /**
     * Creates and returns a CouchDB database for use in testing, populating it
     * with docs from the $dataDir directory.
     */
    static function  setUpBeforeClass() {
        $dbName = "testdb";
        $dataDir = realpath(dirname(__FILE__) . '/../data/couchDocs');
        $showLoc = '/couchdb/shows/by_artifact_type.js';

        // make the test database
        $config = new Zend_Config_Ini(CONFIG_PATH, ENV);
        $couch = new Couch_Client($config->db->dsn,$dbName);

        if ($couch->databaseExists()){
            $couch->deleteDatabase();
        }
        $couch->createDatabase();


        // fill the test database with docs
        $docs = array();
        if ($files = scandir($dataDir)){

            // loop through the directory and add all the docs in it to an array
            foreach ($files as $file){
                if ($file == '.' | $file == '..') continue;
                $docs[] = json_decode(file_get_contents($dataDir . '/' . $file));
            }
        }
        else {
            throw new Exception("Couldn't access the CouchDB data directory at " . $dataDir);
        }

        $couch->storeDocs($docs);

        // make the design doc
        $designDoc = new stdClass();
        $designDoc->_id = "_design/main";

        // get the show
        $shows = new stdClass();
        $by_artifact_typeJson = file_get_contents(APP_PATH . $showLoc);
        $shows->by_artifact_type = $by_artifact_typeJson;
        $designDoc->shows = $shows;

        $couch->storeDoc($designDoc);
    }


    /**
     * this method is important because we need to delete the test database after
     * all the tests in the class have run.
     */
    static function  tearDownAfterClass() {
        $dbName = "testdb";
        $config = new Zend_Config_Ini(CONFIG_PATH, ENV);
        $couch = new Couch_Client($config->db->dsn,$dbName);
        if ($couch->databaseExists()){
            $couch->deleteDatabase();
        }
    }


}


?>
