<?php
/*
 * This is a test for the PHP wrapper for python plugins. To use, substitute your
 * own base URL, then visit
 */


require_once '../../bootstrap.php';
$baseUrl = 'http://total-impact.local'; // replace with the correct one for your install
$testStr = '{"test": true}';

$client = new Zend_Http_Client($baseUrl . '/tests/pluginWrapper/index.php');
$response = $client->setRawData($testStr, "text/json")->request('POST');
$responseStr = $response->getBody();
if ($testStr == $responseStr) {
    $header = "WIN";
    $color = "green";
    $equalityStr = "equals";
}
else {
    $header = "FAIL";
    $color = "red";
    $equalityStr = "doesn't equal";
}
echo "<style type='text/css'>.col {color:$color;}</style>";
echo "<h2 class='col'>$header</h2>";
echo "<p>Test string <span class='col'>$testStr</span> $equalityStr response string <span class='col'>$responseStr</span></p>";

?>
