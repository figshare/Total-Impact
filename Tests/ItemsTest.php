<?php
require_once('../library/simpletest/autorun.php');
require_once('../Models/Item.php');


class TestOfItemsApi extends UnitTestCase {
	private $HTML_BASE;
	
	function setUp(){
		$this->HTML_BASE = "http://localhost/Total-Impact";
	}
	
	function apiGetResponse($call) {
		$request = new HttpRequest($call, HTTP_METH_GET);
		$response = $request->send();
		return($response);
	}

    function testBaseItemsNotImplementedJSON() {
		$response = $this->apiGetResponse($this->HTML_BASE . "/api/v1/items.json");
       	$this->assertIdentical($response->getResponseCode(), 501);
       	$this->assertIdentical($response->getResponseStatus(), "Not Implemented");
   	}

    function testBaseItemsNotImplementedDefault() {
		$response = $this->apiGetResponse($this->HTML_BASE . "/api/v1/items");
       	$this->assertIdentical($response->getResponseCode(), 501);
       	$this->assertIdentical($response->getResponseStatus(), "Not Implemented");
   	}

    function testOnePMIDtoJSON() {
		$id = "17375194";
		$response = $this->apiGetResponse($this->HTML_BASE . "/api/v1/items/" . $id . ".json");
       	$this->assertIdentical($response->getResponseCode(), 200);
		$responseBody = json_decode($response->getBody());
       	$this->assertIsA($responseBody->items, "array");
       	$this->assertIsA($responseBody->items[0]->id, "String");
       	$this->assertIsA($responseBody->items[0]->biblio, "stdClass");
       	$this->assertIsA($responseBody->items[0]->status, "stdClass");
       	$this->assertIsA($responseBody->items[0]->debug, "array");
       	$this->assertIsA($responseBody->items[0]->metrics, "array");
       	$this->assertIsA($responseBody->items[0]->aliases, "stdClass");
   	}

    function testOnePMIDtoJSON_statusonly() {
		$id = "17375194";
		$response = $this->apiGetResponse($this->HTML_BASE . "/api/v1/items/" . $id . ".json?fields=status");
		$responseBody = json_decode($response->getBody());
       	$this->assertWithinMargin((int)$responseBody->items[0]->status->last_updated, time(), 5);
       	$this->assertFalse(property_exists($responseBody->items[0], "biblio"));
       	$this->assertFalse(property_exists($responseBody->items[0], "debug"));
       	$this->assertFalse(property_exists($responseBody->items[0], "metrics"));
       	$this->assertFalse(property_exists($responseBody->items[0], "aliases"));
   	}

    function testOnePMIDtoJSON_biblioonly() {
		$id = "17375194";
		$response = $this->apiGetResponse($this->HTML_BASE . "/api/v1/items/" . $id . ".json?fields=biblio");
		$responseBody = json_decode($response->getBody());
       	$this->assertIsA($responseBody->items[0]->biblio->title, "String");
       	$this->assertFalse(property_exists($responseBody->items[0], "status"));
       	$this->assertFalse(property_exists($responseBody->items[0], "debug"));
       	$this->assertFalse(property_exists($responseBody->items[0], "metrics"));
       	$this->assertFalse(property_exists($responseBody->items[0], "aliases"));
   	}

    function testOnePMIDtoJSON_aliasesonly() {
		$id = "17375194";
		$response = $this->apiGetResponse($this->HTML_BASE . "/api/v1/items/" . $id . ".json?fields=aliases");
		$responseBody = json_decode($response->getBody());
       	$this->assertIsA($responseBody->items[0]->aliases, "stdClass");
       	$this->assertFalse(property_exists($responseBody->items[0], "biblio"));
       	$this->assertFalse(property_exists($responseBody->items[0], "debug"));
       	$this->assertFalse(property_exists($responseBody->items[0], "metrics"));
       	$this->assertFalse(property_exists($responseBody->items[0], "status"));

   	}

    function testTest() {
       	$this->assertIdentical(4, 4);
   	}
}
?>