<?php
require_once dirname(__FILE__) . '/couchDbTestCase.php';
/**
 * this runs unit tests for all the views in couchdb.
 * the wierd classname is so I can leverage Netbeans' autorun for PHPUnit... -j
 */
class couchdb_TestTest extends couchDbTestCase {

    public function testBy_nameWorksForPubMed() {
        $result = $this->couch
                ->key(array("PubMed", "0000000002"))
                ->getView("main", "by_name");
        print_r($result);

        $resultId = ($result->rows) ? $result->rows[0]->id : false;
        $this->assertEquals(
                "2",
                $resultId
                );


    }


}

?>
