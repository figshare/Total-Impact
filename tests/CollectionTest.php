<?php
require_once dirname(__FILE__) . '/../Collection.php';
require_once dirname(__FILE__) . '/../CollectionInput.php';
require_once dirname(__FILE__) . '/../Plugin.php';


class CollectionTest extends PHPUnit_Framework_TestCase {

    private $couch;
    private $collectionToUpdate;
    
    private $testData;
    
    
    function setUp(){
        $testDataFiles = array(
            "collectionWithCrossRef",
            "crossRefPluginResponse",
            "freshCollection",
            "to_updateCouchResponse"
        );

        $this->testData = new stdClass();
        foreach ($testDataFiles as $file) {
            $testDataFromFile = json_decode(file_get_contents('./data/' . $file . '.json'));
            $this->testData->$file = $testDataFromFile;
        }

        $this->couch = $this->getMockBuilder('Couch_Client')
             ->disableOriginalConstructor()
             ->getMock();
        
    }

    /*
     * helper to allow testing of chained methods
     */
    function makeChainable($class){
        $class->expects($this->any())
                ->method($this->anything())
                ->will($this->returnValue($class));
        return $class;
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

    function testUpdateCollectionPreparesPlugin(){
        $this->couch->expects($this->once())
                ->method('getView')
                ->with("main", "to_update")
                ->will($this->returnValue($this->testData->to_updateCouchResponse));
        $this->couch = $this->makeChainable($this->couch);
        
        $plugin = $this->getMock("Plugin");
//        $plugin->expects($this->once())
//                ->method('setArtifactIds');

        $collection = new collection ($this->couch, $plugin);
        $collection->updateCollection();
    }




}

?>
