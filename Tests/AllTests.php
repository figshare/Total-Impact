<?php
require_once('../library/simpletest/autorun.php');
require_once('ItemsTest.php');

class AllTests extends TestSuite {
    function AllTests() {
        $this->TestSuite('All tests');
        $this->addFile('ItemsTest.php');
    }
}
?>