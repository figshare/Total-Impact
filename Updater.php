<?php

/**
 * Handles the creation and update of collections
 *
 * @author jay
 */
class Updater {
    private $couch;
    private $plugin;
    
    public function __construct(Couch_Client $couch, Plugin $plugin){
        $this->couch = $couch;
        $this->plugin = $plugin;
    }
    

    private function fetchCollectionsToUpdate($ts=false) {
        $sourceName = $this->plugin->getName();
        $couchResponse = $this->couch
                ->key($sourceName)
                ->getView("main", "to_update");

        if (!isset($couchResponse->total_rows)) {
            throw new Exception("The database returned something unexpected: '". print_r($couchResponse, true) ."'");
        }

        foreach ($couchResponse->rows as $row){
            // get new data from plugin
            $artifactIds = $row->value;
            $this->plugin->setArtifactIds($artifactIds);
            $pluginResponse = $this->plugin->fetchData();
            if (!isset($pluginResponse->source_name)) { // very basic plugin response validation
                throw new Exception("Got no usable response from the plugin '$sourceName'");
            }

            // update collection in database
            $doc = $this->couch->getDoc($row->_id);
            $doc->sources->$sourceName = $updatedCollection;
            $doc->updates->$sourceName = $ts;
            $this->couch->storeDoc($doc);
        }
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
    
}

?>
