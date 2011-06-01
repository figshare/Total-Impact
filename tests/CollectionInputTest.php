<?php

require_once dirname(__FILE__) . '/../CollectionInput.php';

class CollectionInputTest extends PHPUnit_Framework_TestCase {

    private $ids = array(   "10.1371/journal.pbio.0060048",
                            "10.1371/journal.pbio.0050082",
                            "http://www.slideshare.net/phylogenomics/eisen"
                        );


    function testIdsSplit(){
        $idsStr = implode("\n", $this->ids);
        $obj = new CollectionInput("my title", $idsStr);

        $this->assertEquals(
                $this->ids,
                $obj->getArtifactIds()
                );
    }

    function testCreateCollectionId() {
        $idsStr = implode("\n", $this->ids);
        $obj = new CollectionInput("my title", $idsStr);

        $this->assertEquals(
                5,
                strlen($obj->getCollectionId())
                );
    }

}

?>
