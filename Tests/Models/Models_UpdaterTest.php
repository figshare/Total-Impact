<?php
//require_once dirname(__FILE__) . '/../Updater.php';
//require_once dirname(__FILE__) . '/../CollectionInput.php';
//require_once dirname(__FILE__) . '/../Plugin.php';


class Models_UpdaterTest extends PHPUnit_Framework_TestCase {

    private $couch;
    private $collectionToUpdate;
    
    
    /**
     * helper to get json-encoded test data
     *
     * @param string $fileName name of a file in the tests/data dir
     * @return an object deserialised from location specified in $filename
     */
    function getData($fileName){
        $fileContents = file_get_contents('../data/' . $fileName . '.json');
        $commentsRemoved = preg_replace('# //.+?$#m', '', $fileContents);
        return json_decode($commentsRemoved);
    } 


    function setUp(){
        $this->fakeCouch = new Tests_Fakes_Couch();
        
    }

    function testUpdateCollection(){
        $to_updateViewResponse = $this->getData('freshToMendeley/to_updateViewResponse');
        $pluginQuery = $this->getData('freshToMendeley/pluginQuery');
        $pluginResponse = $this->getData('freshToMendeley/pluginResponse');
        $updatedDoc = $this->getData('couchDocs/2');
        $freshDoc = $this->getData('couchDocs/1');


        $this->fakeCouch->setViewReturns(array($to_updateViewResponse));
        $this->fakeCouch->setDocsToGet(array("abcdef"=>$freshDoc));

        $plugin = $this->getMock("Models_Plugin");
        $plugin->expects($this->once())
                ->method('setArtifactIds')
                ->with($pluginQuery);
        $plugin->expects($this->any())
                ->method('getName')
                ->will($this->returnValue("Mendeley"));
        $plugin->expects($this->once())
                ->method('fetchData')
                ->will($this->returnValue($pluginResponse));


        $updater = new Models_Updater ($this->fakeCouch, $plugin);
        $updater->update("1306821630");
        $this->assertEquals(
                $updatedDoc,
                $updater->getCouch()->getStoredDocs(0)
                );

        
    }





}

?>
