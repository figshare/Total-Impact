<?php

class Models_Provider_GitHubOrgTest extends PHPUnit_Framework_TestCase {

    protected $obj;

    protected function setUp() {
        $this->obj = new Models_Provider_GitHubOrg();
        $adapter = new Zend_Http_Client_Adapter_Test();
        $this->http = new Zend_Http_Client();
        $this->creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');
    }


    public function testFetchLinks() {
        $response = $this->obj->fetchLinks("bioperl", $this->http, $this->creds);
        print_r($response);
        $this->assertContains(
                array("namespace"=>"GitHub", "id" => "bioperl/Bio-Community"),
                $response
                );
    }
}
?>
