<?php

class Models_Provider_SlideshareTest extends Zend_Test_PHPUnit_ControllerTestCase {

    protected $obj;

    protected function setUp() {
        $this->obj = new Models_Provider_Slideshare();
        $adapter = new Zend_Http_Client_Adapter_Test();
        $this->http = new Zend_Http_Client();
        $this->creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');
    }

    public function testfetchLinks() {
        $response = $this->obj->fetchLinks("cavlec", $this->http, $this->creds);
        print_r($response);
        $this->assertContains(
                array("namespace"=>"Slideshare", "id" => "cavlec/rdf-rda-and-other-tlas"),
                $response
                );
    }

}

?>
