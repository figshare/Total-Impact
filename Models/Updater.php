<?php

/**
 * Handles the creation and update of collections
 *
 * @author jay
 */
class Models_Updater {
    private $couch;
    private $plugin;
    
    public function __construct(Couch_Client $couch, Models_Plugin $plugin){
        $this->couch = $couch;
        $this->plugin = $plugin;
    }
    public function getCouch() {
        return $this->couch;
    }

    /**
     * Gets all the collections that haven't yet been successfully updated by the loaded plugin,
     *    gets plugin results for each one, then stores the updated docs.
     *
     *
     * @param string optional timestamp, useful for testing
     * @return bool true on success
     */
    public function update($ts=false, $collectionId=false) {
        $sourceName = $this->plugin->getName();
        $couchResponse = $this->couch
                ->key($sourceName)
                ->getView("main", "to_update");

        if (!isset($couchResponse->total_rows)) {
            throw new Exception("The database returned something unexpected: '". print_r($couchResponse, true) ."'");
        }

        foreach ($couchResponse->rows as $row) {
	
			// Eventually we need to update everything, but right now it is troublesome when there are so many debugging fragments
			if ($row->id != $collectionId) {
				continue;
			}
			
            // get new data from plugin
            $artifactIds = $row->value;
            $this->plugin->setArtifactIds($artifactIds);
			
            $fetchedData = $this->plugin->fetchData();
            $body = $fetchedData->getBody();
            $pluginResponse = json_decode($body);

            if (!isset($pluginResponse->source_name)) { 
				// very basic plugin response validation.  What is appropriate here?
                #throw new Exception("Got no usable response from the plugin '$sourceName' at " . $this->plugin->getUri()
                #        . "; instead got this:\n " 
                #        . print_r($pluginResponse, true)
                #        . "\n");
            }

            $this->updateDoc($row->id, $sourceName, $pluginResponse, 0, $ts);
        }
        return true;
    }

    /**
     * Updates a given couch doc with information from the plugin
     * Tries again a few times if storage doesn't work (generally that's because
     *    the doc was changed after we downloaded it).
     *
     * @param string $docId
     * @param string $sourceName
     * @param StdClass $pluginResponse
     * @param int $tries
     * @param bool|string $ts
     * @return StdClass CouchDB response object
     */
    private function updateDoc($docId, $sourceName, $pluginResponse, $tries, $ts){
        $maxTries = 5;
        $ts = ($ts) ? $ts : (string)time();
        $doc = $this->couch->getDoc($docId);
        $doc->sources->$sourceName = $pluginResponse;
        if (!$pluginResponse->has_error){
            $doc->updates->$sourceName = $ts;
        }
        else {
            $doc->updates->$sourceName = false;
        }
        try {
            $response = $this->couch->storeDoc($doc);
        }
        catch (Exception $e){
            if ($tries > $maxTries) {
                throw $e;
            }
            sleep(1);
            return $this->updateDoc($docId, $sourceName, $pluginResponse, $tries+1, $ts);
        }

        return $response;
    }


    
}

?>
