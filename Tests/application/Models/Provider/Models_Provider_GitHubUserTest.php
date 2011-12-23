<?php

class Models_Provider_GitHubUserTest extends PHPUnit_Framework_TestCase {

    protected $obj;

    protected function setUp() {
        $this->obj = new Models_Provider_GitHubUser();
        $adapter = new Zend_Http_Client_Adapter_Test();
        $this->http = new Zend_Http_Client();
        $this->creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');
    }


    public function testFetchLinks() {
        $response = $this->obj->fetchLinks("egonw", $this->http, $this->creds);
        $this->assertContains(
                array("namespace"=>"GitHub", "id" => "egonw/gtd"),
                $response
                );
    }
}
?>
