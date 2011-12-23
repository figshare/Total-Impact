<?php
/*
 * this makes an array of test documents that are used to test couchdb views.
 * because of this, it also defines how these docs should be formatted.
 * you can test the views with the couchdbtesttest.php class.
 */

function makeTestDocs($num) {
    $testDocs = array();
    for ($i=1; $i<$num; $i++) {
        $doc = new stdClass();
        $doc->_id = (string)$i;
        $doc->type = "item";
        $doc->created_at = date("c"); // current time in ISO 8601 format, plus microseconds
        $doc->metrics = new stdClass();
        $doc->metrics->GitHub = new stdClass();  // add more metrics when we need them...
        $doc->aliases = new stdClass();
        $testDocs[$i] = $doc;
    }

    $testDocs[1]->aliases->DOI = "10.fakedoi-1";
    $testDocs[1]->aliases->URL = "http://www.fakejournal.org/1";
    $testDocs[1]->aliases->PubMed = "0000000001";
    $testDocs[1]->aliases->Dryad = "1";

    $testDocs[2]->aliases->DOI = "10.fakedoi-2";
    $testDocs[1]->aliases->URL = "http://www.fakejournal.org/2";
    $testDocs[2]->aliases->PubMed = "0000000002";
    $testDocs[2]->aliases->Mendeley = "000-000-001";

    $testDocs[3]->aliases->URL = "http://www.example.com";

    $testDocs[4]->aliases->URL = "http://www.github.com/jasonpriem";
    $testDocs[4]->aliases->GitHub = "jasonpriem";

    return $testDocs;
}



?>
