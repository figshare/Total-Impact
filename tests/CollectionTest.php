<?php
require_once dirname(__FILE__) . '/../Collection.php';
require_once dirname(__FILE__) . '/../CollectionInput.php';


class CollectionTest extends PHPUnit_Framework_TestCase {

    private $artifactIds = array( "10.1371/journal.pbio.0060048",
                                  "10.1371/journal.pbio.0050082",
                                  "http://www.slideshare.net/phylogenomics/eisen"
                        );
    private $collectionTitle = "my title";
    private $collectionId = "abcde";

    public function testMake() {
        $couch = $this->getMockBuilder('Couch_Client')
             ->disableOriginalConstructor()
             ->getMock();
        $couch->expects($this->once())
                ->method('storeDoc');

        $input = $this->getMockBuilder('CollectionInput')
             ->disableOriginalConstructor()
             ->getMock();
        $input->expects($this->once())
                ->method('getCollectionTitle')
                ->will($this->returnValue($this->collectionTitle));
        $input->expects($this->once())
                ->method('getArtifactIds')
                ->will($this->returnValue($this->artifactIds));
        $input->expects($this->once())
                ->method('getCollectionId')
                ->will($this->returnValue($this->collectionId));

        $collection = new Collection($couch);
        $collection->make($input);
    }





}

?>
