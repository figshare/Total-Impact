<?php

class Models_Provider_GitHubOrgTest extends PHPUnit_Framework_TestCase {

    protected $obj;

    protected function setUp() {
        $adapter = new Zend_Http_Client_Adapter_Test();
        $http = new Zend_Http_Client();
        $creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');
        $this->obj = new Models_Provider_GitHubOrg($http, $creds);
    }


    public function testFetchLinks() {
        $response = $this->obj->fetchLinks("bioperl");
        $this->assertContains(
                array("namespace"=>"GitHub", "id" => "bioperl/Bio-Community"),
                $response
                );
    }
}
?>
