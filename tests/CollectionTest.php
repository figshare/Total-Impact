<?php
require_once dirname(__FILE__) . '/../Collection.php';
require_once dirname(__FILE__) . '/../CollectionInput.php';
require_once dirname(__FILE__) . '/../Plugin.php';


class CollectionTest extends PHPUnit_Framework_TestCase {

    private $couch;
    private $collectionToUpdate;
    
    
    
    function setUp(){
        $this->couch = $this->getMockBuilder('Couch_Client')
             ->disableOriginalConstructor()
             ->getMock();
        $this->fakeCouch = new FakeCouch();
        
    }

    /*
     * helper to get json-encoded test data
     */
    function getData($fileName){
        $fileContents = file_get_contents('./data/' . $fileName . '.json');
        $commentsRemoved = preg_replace('# //.+?$#m', '', $fileContents);
        echo $commentsRemoved;
        return json_decode($commentsRemoved);
    }



    function testMake() {
        $couch = $this->couch;
        $couch->expects($this->once())
                ->method('storeDoc');
        $plugin = $this->getMock('Plugin');

        $input = $this->getMockBuilder('CollectionInput')
             ->disableOriginalConstructor()
             ->getMock();
        $input->expects($this->once())->method('getCollectionTitle');
        $input->expects($this->once())->method('getArtifactIds');
        $input->expects($this->once())->method('getCollectionId');

        $collection = new Collection($couch, $plugin);
        $collection->make($input);
    }


    function testUpdateCollection(){
        $to_updateResponse = $this->getData('freshToMendeley/to_updateResponse');
        $myPluginQuery = $this->getData('freshToMendeley/pluginQuery');
        $myPluginResponse = $this->getData('freshToMendeley/pluginResponse');
        $updatedDoc = $this->getData('couchDocs/withMendeley');
        

        $this->fakeCouch->setViewReturns(array($to_updateResponse));
        
        $plugin = $this->getMock("Plugin");
        $plugin->expects($this->once())
                ->method('setArtifactIds')
                ->with($myPluginQuery);
        $plugin->expects($this->any())
                ->method('getName')
                ->will($this->returnValue("mendeley"));
        $plugin->expects($this->once())
                ->method('fetchData')
                ->will($this->returnValue($myPluginResponse));


        $collection = new collection ($this->fakeCouch, $plugin);
        $collection->updatedQueuedCollection("1306821630");
        $this->assertEquals(
                $updatedDoc,
                $collection->getCouch()->getStoredDocs(0)
                );
    }





}

?>
