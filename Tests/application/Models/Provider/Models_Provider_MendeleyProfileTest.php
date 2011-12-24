<?php

require_once dirname(__FILE__) . '/../../../../application/Models/Provider/MendeleyProfile.php';

/**
 * Test class for Models_Provider_MendeleyProfile.
 * Generated by PHPUnit on 2011-12-22 at 06:43:47.
 */
class Models_Provider_MendeleyProfileTest extends PHPUnit_Framework_TestCase {

    protected $obj;

    protected function setUp() {
        $this->obj = new Models_Provider_MendeleyProfile();
        $adapter = new Zend_Http_Client_Adapter_Test();
        $this->http = new Zend_Http_Client();
        $this->creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');
    }


    public function testFetchLinks() {
        $response = $this->obj->fetchLinks("heather-piwowar", $this->http, $this->creds);
        print_r($response);

        $this->assertContains(
                array("namespace"=>"PubMed", "id" => "18998885"),
                $response
                );
        $this->assertContains(
                array("namespace"=>"DOI", "id" => "10.1038/npre.2008.1701.1"),
                $response
                );
    }
}
?>