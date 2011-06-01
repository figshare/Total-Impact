<?php
require_once dirname(__FILE__) . '/../Collection.php';


class CollectionTest extends PHPUnit_Framework_TestCase {



    protected function setUp() {
        $this->title = "Jason's collection";
        $this->idsStr = "10.1371/journal.pbio.0060048\n10.1371/journal.pbio.0050082\nhttp://www.slideshare.net/phylogenomics/eisen\n";
    }

    public function testMake() {
        $couch = $this->getMockBuilder('Couch_Client')
             ->disableOriginalConstructor()
             ->getMock();
        $couch->expects($this->once())
                ->method('storeDoc');

        $collection = new Collection($couch);
        $collection->make($this->title, $this->idsStr);
    }

}

?>
