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
    private $plugin;
    
    function __construct(Couch_Client $couch, Plugin $plugin) {
        $this->couch = $couch;
        $this->plugin = $plugin;
    }

    /**
     * Creates a new collection based on user input
     *
     * @param CollectionInput $input
     * @return Object couchdb response object
     */
    public function make(CollectionInput $input) {
        
        // build the object
        $ts = time();
        $id = $input->getCollectionId();

        $this->doc = new stdClass();
        $this->doc->_id = $id;
        $this->doc->created_at = $ts;
        $this->doc->title = $input->getCollectionTitle();
        $this->doc->artifact_ids = $input->getArtifactIds();
        $this->doc->sources = new stdClass(); // we'll fill this later
        
        // put it in couchdb
        $response = $this->couch->storeDoc($this->doc);
        return $response;
    }

    public function updateCollection() {
        $res = $this->couch
                ->limit(1)
                ->key('mySourceName')
                ->include_docs(true)
                ->getView("main", "to_update");



        
    }
    
}
    

?>
