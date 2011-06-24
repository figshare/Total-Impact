<?php


class by_artifactTest extends Tests_CouchDB_TestCase {

    private $couch;
    private $expectedShows;

    /**
     * Keeps all-numeric string keys from being transformed to ints by prepending "a" to them.
     * I'm not sure where this bug is, but somewhere in the comparison it makes the conversion,
     * causing a "undefined index" error. This fixes that.
     *
     * @param StdClass $obj
     */
    function fixNumStrKeys(StdClass $obj) {
        $str = json_encode($obj);
        $newStr = preg_replace('#"(\d+)":#', '"a$1":', $str);
        echo $newStr;
        $newObj = json_decode($newStr);
        var_dump($newObj);
        return $newObj;
    }

    function  setUp() {
        $dbName = "testdb";
        $expectedShowsDir = APP_PATH . "/Tests/data/by_artifactReturns";
        $expectedShowsAvail = array('6');
        
        $config = new Zend_Config_Ini(CONFIG_PATH, ENV);
        $this->couch = new Couch_Client($config->db->dsn,$dbName);
        $this->expectedShows = array();
        foreach ($expectedShowsAvail as $showName){
            $showLoc = $expectedShowsDir . "/$showName.json";
            $this->expectedShows[$showName] = json_decode(file_get_contents($showLoc));
        }
        
        
        
    }


    function testThisCrazyThingRunsAtAll(){
        $this->assertEquals(
                9,
                9
                );
    }


    function testShowReturnsExpectedFor6() {
        $showObj = json_decode($this->couch->getShow('main', 'by_artifact', 6));

        $this->assertEquals(
                $this->fixNumStrKeys($this->expectedShows['6']),
                $this->fixNumStrKeys($showObj)
                );
    }


 





}

?>
