<?php

//require_once dirname(__FILE__) . '/../CollectionInput.php';

class Models_CollectionInputTest extends PHPUnit_Framework_TestCase {

    private $ids = array(   "10.1371/journal.pbio.0060048",
                            "10.1371/journal.pbio.0050082",
                            "http://www.slideshare.net/phylogenomics/eisen"
                        );
    private $collection;

    function setUp(){
        $this->collection = json_decode(
                '{
                    "title": "my title",
                    "created_at": "1234567890",
                    "artifact_ids": [
                        "10.1371/journal.pbio.0060048",
                        "10.1371/journal.pbio.0050082",
                        "http://www.slideshare.net/phylogenomics/eisen"
                    ],
                    "sources":{},
                    "updates":{}

                }'
                );
    }


    function testMake(){
        $idsStr = implode("\n", $this->ids);
        $fakeCouch = new Tests_Fakes_Couch();
        $ci = new Models_CollectionInput($fakeCouch);
        $ci->save("my title", $idsStr, "1234567890");
        $res = $fakeCouch->getStoredDocs(0);
        $exp = $this->collection;

        $this->assertEquals(
                $exp->artifact_ids,
                $res->artifact_ids
                );
        $this->assertEquals(
                $exp->sources,
                $res->sources
                );
        $this->assertEquals(
                $exp->updates,
                $res->updates
                );
        $this->assertEquals(
                6,
                strlen($res->_id)
                );
    }


}

?>
