<?php


class by_artifact_typeTest extends Tests_CouchDB_TestCase {

    function  __construct() {
        parent::__construct();

        // make the design doc
        $designDoc = new stdClass();
        $designDoc->_id = "_design/test";

        // get the show
        $shows = new stdClass();
        $by_artifact_typeJson = file_get_contents(APP_PATH . '/couchdb/shows/by_artifact_type.js');
        $shows->by_artifact_type = $by_artifact_typeJson;
        $designDoc->shows = $shows;

        $this->couch->storeDoc($designDoc);
    }


    function testThisCrazyThingRunsAtAll(){
        $this->assertEquals(
                9,
                9
                );
    }





}

?>
