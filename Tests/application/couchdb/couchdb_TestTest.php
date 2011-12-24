<?php
require_once 'application/couchdb/couchDbTestCase.php';
/**
 * this runs unit tests for all the views in couchdb.
 * the wierd classname is so I can leverage Netbeans' autorun for PHPUnit... -j
 */
class couchdb_TestTest extends couchDbTestCase {

    public function testBy_nameWorksForPubMedAlias() {
        $result = $this->couch
                ->key(array("PubMed", "0000000002"))
                ->getView("main", "by_name");

        $resultId = ($result->rows) ? $result->rows[0]->id : false;
        $this->assertEquals(
                "2",
                $resultId
                );
    }

    public function testBy_nameWorksForTotalImpactAlias() {
        $result = $this->couch
                ->key(array("totalimpact", "4"))
                ->getView("main", "by_name");

        $resultId = ($result->rows) ? $result->rows[0]->id : false;
        $this->assertEquals(
                "4",
                $resultId
                );
    }

    public function testBy_nameReturnsNothingForIdItDoesntHave() {
        $result = $this->couch
                ->key(array("URL", "http://www.siteitdoesnthave.com"))
                ->getView("main", "by_name");

        $resultId = ($result->rows) ? $result->rows[0]->id : false;
        $this->assertEmpty($result->rows);
    }


}

?>
