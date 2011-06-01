<?php

/**
 *  Handles the uploading of artifact collections.
 *
 * @author jay
 */
class Collection {
    private $doc;
    private $title;
    private $idsStr;
    private $couch;
    
    function __construct(Couch_Client $couch) {
        $this->couch = $couch;
        $this->title = $title;
        $this->idsStr = $str;
    }

    

    
    function make($title, $idsStr) {
        
        // build the object
        $ts = time();
        $id = $this->randStr(5);

        $this->doc = new stdClass();
        $this->doc->_id = $id;
        $this->doc->created_at = $ts;
        $this->doc->title = $title;
        $this->doc->artifact_ids = $this->parseLines($idsStr);
        $this->doc->sources = new stdClass(); // we'll fill this later
        
        // put it in couchdb
        $response = $this->couch->storeDoc($this->doc);
        return $response->id;
    }
    
}
    

?>
