<?php
 #used to test the max number of parallel requests we can make.
function queryPlugins() {
    $response = new stdClass();
    $pool = new HttpRequestPool();
    $pluginUrls = array();

    for ($i=0; $i<10; $i++) {
        $pluginUrls[] = "http://localhost/plugins/metrics/Wikipedia/index.cgi?foo=" . $i;
    }

    foreach ($pluginUrls as $sourceName => $pluginUrl) {
        $request = new HttpRequest($pluginUrl, HTTP_METH_GET);

        $dataToSend = "{}";
        $encoded_data = json_encode($dataToSend);
	$request->setPostFields(array('query' => $encoded_data));
        $pool->attach($request);
    }

    $pool->send();

    foreach ($pool as $request) {
        $body = $request->getResponseBody();
        echo $body . "<br>*****************************************<br><br><br>";
    }
}

function testHttpPool() {
    $startTime = microtime(TRUE);
    
    $pluginUrls = array();
    for ($i=0; $i<15; $i++) {
        $pluginUrls[] = "http://localhost";
    }

    $pool = new HttpRequestPool();
    foreach ($pluginUrls as $k => $url) {
        $request = new HttpRequest($url, HTTP_METH_GET);
        $pool->attach($request);
    }
    $pool->send();
    $finishedRequests = $pool->getAttachedRequests();

    foreach($finishedRequests as $request) {
        print_r($request->getResponseInfo());
    }
    echo "total time: " . (microtime(TRUE) - $startTime);
}

testHttpPool();

?>
