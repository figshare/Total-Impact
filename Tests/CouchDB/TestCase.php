<?php
/**
 * Creates a CouchDB database using the project's config file and the documents
 * contained in the Tests/data/couchDocs folder, then makes that available as
 * a PHP-on-couch object.
 */

class Tests_CouchDB_TestCase extends PHPUnit_Framework_TestCase {

    protected $couch;
    protected $dbName;
    private $dataDir;

    function  __construct() {
        $this->dbName = "test-" . mt_rand(0, 1000000);
        $this->dataDir = realpath(dirname(__FILE__) . '/../data/couchDocs');

        $this->couch = $this->makeDB($this->dbName);
    }


    /**
     * this method is important because we need to delete the test database after
     * all the tests in the class have run.
     */
    function  __destruct() {
        // try-catch block is because exceptions thrown in desctructors have no context.
        try {
            $this->couch->deleteDatabase();
        }
        catch (Exception$e) {
            echo $e->__toString();
        }
    }

    /**
     * Creates and returns a CouchDB database for use in testing, populating it
     * with docs from the $dataDir directory.
     *
     * @param string $name The name for the temporary test database
     * @return Couch_Client
     */
    private function makeDB($name) {
        $config = new Zend_Config_Ini(CONFIG_PATH, ENV);
        $couch = new Couch_Client($config->db->dsn,$name);

        if (!$couch->databaseExists()){
            $couch->createDatabase();
        }

        $docs = array();
        if ($files = scandir($this->dataDir)){

            // loop through the directory and add all the docs in it to an array
            foreach ($files as $file){
                if ($file == '.' | $file == '..') continue;
                $docs[] = json_decode(file_get_contents($this->dataDir . '/' . $file));
            }
        }
        else {
            throw new Exception("Couldn't access the CouchDB data directory at " . $this->dataDir);
        }

        $couch->storeDocs($docs);
        return $couch;
    }






}


?>
