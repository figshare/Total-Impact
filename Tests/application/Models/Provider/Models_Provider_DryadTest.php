<?php

class Models_Provider_DryadTest extends Zend_Test_PHPUnit_ControllerTestCase {

    protected $obj;

    protected function setUp() {
        
        $this->obj = new Models_Provider_Dryad;
        $adapter = new Zend_Http_Client_Adapter_Test();
        $this->http = new Zend_Http_Client();
        $this->creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');
    }

    protected function tearDown() {

    }

    public function testFetchLinks() {
        $response = $this->obj->fetchLinks("Otto, Sarah P.", $this->http, $this->creds);
        print_r($response);
        $this->assertContains(
                array("namespace"=>"Dryad", "id" => "18"),
                $response
                );
    }


}

?>
