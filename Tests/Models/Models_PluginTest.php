<?php

class Models_PluginTest extends PHPUnit_Framework_TestCase {
    
    private $obj;
    
    function setUp() {
        $this->obj = new Models_Plugin();
    }


    /**
     * A goofy basic test to make sure that I got inheritance right
     */
    function testFetchData(){
        $this->obj->setArtifactIds(new stdClass);
        $this->obj->setUri("http://www.example.com");
        $this->obj->fetchData();
    }


}

?>
