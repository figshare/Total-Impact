<?php

class Models_Provider_DryadTest extends Zend_Test_PHPUnit_ControllerTestCase {

    protected $obj;
    protected $aliasesObj = array(
        "DOI" => "10.1038/nature04863",
        "PubMed" => "0000000001"
    );
    protected $aliasesObjNoHits = array(
        "DOI" => "10.100.fake/1",
        "PubMed" => "0000000001"
    );

    protected function setUp() {
        $adapter = new Zend_Http_Client_Adapter_Test();
        $http = new Zend_Http_Client();
        $creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');
        $this->aliasesObj = new Models_Aliases($this->aliasesObj);

        $this->obj = new Models_Provider_Dryad($http, $creds);
    }

    protected function tearDown() {

    }

    public function testFetchLinks() {
        $response = $this->obj->fetchLinks("Otto, Sarah P.");
        $this->assertContains(
                array("namespace"=>"Dryad", "id" => "18"),
                $response
                );
    }

    public function testAddAliases() {
        $dryadDoi = $this->obj->addAliases($this->aliasesObj);
        print_r($dryadDoi);
        $this->assertEquals(
                "8426",
                $this->aliasesObj->getId("Dryad")
                );
    }


}

?>
