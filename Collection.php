<?php

/**
 * Description of Collection
 *
 * @author jay
 */
class Collection {
    private $title;
    private $idsStr;
    private $couch;
    
    function __construct(Couch_Client $couch, $title, $str) {
        $this->couch = $couch;
        $this->title = $title;
        $this->idsStr = $str;
    }
    
    private function makeId($len) {
        $str = md5(microtime());
        $shortStr = substr($str, 0, $len);
        return $shortStr;
    }
    
    private function parseLines($str){
        $lines = preg_split("/[\s,]+/", $str);
        return $lines;
    }
    
    function make() {
        
        // build the object
        $ts = time();
        $id = $this->makeId(6);
        $doc = new stdClass();
        $doc->_id = $id;
        $doc->created_at = $ts;
        $doc->updated = false;
        $doc->title = $this->title;
        $doc->artifact_ids = $this->parseLines($this->idsStr);
        $doc->sources = new stdClass(); // we'll fill this later
        
        // put it in couchdb
        $response = $this->couch->storeDoc($doc);
        print_r($response);
        return $response->id;
    }
    
}
    

?>
