<?php


class by_artifact_typeTest extends Tests_CouchDB_TestCase {

    private $couch;

    function  setUp() {
        $dbName = "testdb";
        $config = new Zend_Config_Ini(CONFIG_PATH, ENV);
        $this->couch = new Couch_Client($config->db->dsn,$dbName);
    }


    function testThisCrazyThingRunsAtAll(){
        $this->assertEquals(
                9,
                9
                );
    }

    function testShowReturnsAnything() {
        $showRes = $this->couch->getShow('main', 'by_artifact_type', 6);
        $this->assertTrue($showRes);

    }

 





}

?>
