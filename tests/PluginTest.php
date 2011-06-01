<?php
require_once dirname(__FILE__) . '/../Plugin.php';


class PluginTest extends PHPUnit_Framework_TestCase {
    
    private $obj;
    
    function setUp() {
        $this->obj = new Plugin();
    }

    // yes, testing getters and setters is stupid. This here because I think
    //      we may want more tests on this later.
    function testSetName(){
        $name = "my plugin name";
        $this->obj->setName($name);
        $this->assertEquals(
                $name,
                $this->obj->getName()
                );
        
    }
}

?>
