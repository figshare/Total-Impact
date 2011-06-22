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
    function getJson($fileName){
        $fileContents = file_get_contents('../data/' . $fileName . '.json');
        $commentsRemoved = preg_replace('# //.+?$#m', '', $fileContents);
        return $commentsRemoved;
    } 


    function setUp(){
        $this->fakeCouch = new Tests_Fakes_Couch();
        
    }

    function testUpdateCollection(){
        $to_updateViewResponse = $this->getJson('freshToMendeley/to_updateViewResponse');
        $pluginQuery = $this->getJson('freshToMendeley/pluginQuery');
        $pluginResponse = $this->getJson('freshToMendeley/pluginResponse');
        $updatedDoc = $this->getJson('freshToMendeley/withMendeley');
        $freshDoc = $this->getJson('freshToMendeley/fresh');

        $adapter = new Zend_Http_Client_Adapter_Test();
        $adapter->setResponse(
                "HTTP/1.1 400 OK"         . "\r\n" .
                "Location: /"             . "\r\n" .
                "Content-Type: text"      . "\r\n" .
                                            "\r\n" .
                $pluginResponse
                );

        $plugin = new Models_Plugin("http://www.example.com", array('adapter' => $adapter));
        $plugin->setName('Mendeley');


        $this->fakeCouch->setViewReturns(array(json_decode($to_updateViewResponse)));
        $this->fakeCouch->setDocsToGet(array("abcdef"=>json_decode($freshDoc)));

//        $plugin = $this->getMock("Models_Plugin");
//        $plugin->expects($this->once())
//                ->method('setArtifactIds')
//                ->with($pluginQuery);
//        $plugin->expects($this->any())
//                ->method('getName')
//                ->will($this->returnValue("Mendeley"));
//        $plugin->expects($this->once())
//                ->method('fetchData')
//                ->will($this->returnValue($pluginResponse));


        $updater = new Models_Updater ($this->fakeCouch, $plugin);
        $updater->update("1306821630");
        $this->assertEquals(
                json_decode($updatedDoc),
                $updater->getCouch()->getStoredDocs(0)
                );

        
    }





}

?>
