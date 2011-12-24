<?php
require_once  realpath(dirname(__FILE__) )  . '/makeTestDocs.php';

class couchDbTestCase extends PHPUnit_Framework_TestCase {


    protected $object;
    protected $couch;
    static $numTestDocs = 5;


    public static function setUpBeforeClass() {
        // setup the test db
        $creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini', 'db');
        $dbName = "mytestdb";
        $couch = new Couch_Client($creds->dsn, $dbName);
        if ($couch->databaseExists()) {
            $couch->deleteDatabase();
        }
        $couch->createDatabase();

        // populate the test db
        $docs = makeTestDocs(self::$numTestDocs); // function from the included ./testDocs.php file
        $couch->storeDocs($docs);

        // make the design doc (should refactor this block to a diff function)...
        $designDoc = new stdClass();
        $designDoc->_id = "_design/main";
        $designDoc->views = new stdClass();
        $map = file_get_contents(APPLICATION_PATH . '/couchdb/views/by_name.js');
        $view = new stdClass();
        $view->map = $map;
        $designDoc->views->by_name = $view;
        $couch->storeDoc($designDoc);
    }


    public static function tearDownAfterClass() {

    }

    protected function setUp() {
        $this->object = new couchdb_Test;
        $creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini', 'db');
        $this->couch = new Couch_Client($creds->dsn, "mytestdb");

    }

    public function testNewCouchDbGotCreated() {
        $this->assertTrue($this->couch->databaseExists());
    }


}

?>
