<?php

/**
 *  Creates and updates collections of artifacts
 *
 * @author jason
 */
class Collection {
    private $doc;
    private $title;
    private $idsStr;
    private $couch;
    private $plugin;
    private $couchResponse;
    private $updatedDoc;
    
    function __construct(Couch_Client $couch, Plugin $plugin) {
        $this->couch = $couch;
        $this->plugin = $plugin;
    }
    public function getCouch() {
        return $this->couch;
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

        $doc = new stdClass();
        $doc->_id = $id;
        $doc->created_at = $ts;
        $doc->title = $input->getCollectionTitle();
        $doc->artifact_ids = $input->getArtifactIds();
        $doc->sources = new stdClass(); // we'll fill this later
        $doc->updates = new stdClass(); // also for later
        
        // put it in couchdb
        $response = $this->couch->storeDoc($doc);
        return $response;
    }

    /**
     * Uses the loaded plugin to update a collection supplied by a CouchDB view.
     *
     *
     * @param Int $pauseSecs Seconds to pause after updating, to let documents finish uploading.
     * @param String $ts Timestamp, a hack for testing
     * @return Boolean TRUE if the db returned a collection FALSE otherwise
     */
    public function updatedQueuedCollection($ts=false){
        $storedCollection = $this->fetchCollectionToUpdate();
        if ($storedCollection) {
            $updatedCollection = $this->fetchPluginData($storedCollection, $ts);
            $this->couch->storeDoc($updatedCollection);
            return true;
        }
        else {
            return false;
        }

    }

    /**
     * Gets a single collection (from the DB) that needs to be updated by the loaded plugin
     *
     * @return StdClass|Bool The response object from CouchDb, or FALSE if there are no more left
     */
    private function fetchCollectionToUpdate() {
        $couchResponse = $this->couch
                ->limit(1)
                ->key($this->plugin->getName())
                ->include_docs(true)
                ->getView("main", "to_update");

        if (!isset($couchResponse->total_rows)) {
            throw new Exception("The database returned something unexpected: '". print_r($couchResponse, true) ."'");
        }
        elseif ($couchResponse->total_rows === 0) {
            return false;
        }
        else { // we got a good response back from the database.
            return $couchResponse;
        }
    }

    /**
     * Gets data from the loaded plugin about a given collection
     *
     * @param stdClass $couchResponse Response object from CouchDB
     * @param String $ts $timestamp, just for testing
     * @return StdClass response object from plugins
     */
    private function fetchPluginData(stdClass $couchResponse, $ts){
        $ts = ($ts) ? $ts : (string)$time;
        $this->plugin->setArtifactIds($couchResponse->rows[0]->value);
        $pluginResponse = $this->plugin->fetchData();

        if (!isset($pluginResponse->sourceName)) { // very basic plugin response validation

            $doc = $couchResponse->rows[0]->doc;
            $sourceName = $this->plugin->getName();
            
            $doc->sources->$sourceName = $pluginResponse;
            $doc->updates->$sourceName = $ts;

            return $doc;
        }
        else {
            throw new Exception("Got no usable response from the plugin '" . $this->plugin->getName() . "'");
        }
    }


    
}
    

?>
