<?php
/**
 * Description of Collection
 *
 * @author jason
 */
class Models_Collection {

   /**
    * Splits a string into lines
    *
    * @param String $str
    * @return Array the lines from the string
    */
   private function idsFromStr($str){
        $lines = preg_split("/[\s,]+/", $str);
        return $lines;
   }

    /**
     * Returns a string of random, mixed-case letters
     *
     * @param Int $length Length you want the returned ID to be
     * @return string
     */
    private function randStr($length) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $ret = "";

		$size = strlen($chars);
		for($i = 0; $i < $length; $i++) {
	            $index = mt_rand(0, $size - 1);
			$ret .= $chars[$index];
		}
		return $ret;
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
    private function updateDoc($docId, $sourceName, $pluginResponse, $tries, $ts, $couch){
        $maxTries = 5;
        $ts = ($ts) ? $ts : (string)time();
        $doc = $couch->getDoc($docId);
        $doc->sources->$sourceName = $pluginResponse;
        if (!$pluginResponse->has_error){
            $doc->updates->$sourceName = $ts;
        }
        else {
            $doc->updates->$sourceName = false;
        }
        try {
            $response = $couch->storeDoc($doc);
        }
        catch (Exception $e){
            if ($tries > $maxTries) {
                throw $e;
            }
            sleep(1);
            return $this->updateDoc($docId, $sourceName, $pluginResponse, $tries+1, $ts, $couch);
        }

        return $response;
    }


	public function update($collectionId, $config) {
        $couch = new Couch_Client($config->db->dsn, $config->db->name);
		$doc = $this->fetch($collectionId, $couch);
		$artifact_ids = $doc->artifact_ids;
		$artifact_class = new stdClass();
        foreach ($artifact_ids as $index => $id) {
			$artifact_class->$id = new stdClass();
			$artifact_class->$id->doi = "";
			$artifact_class->$id->url = "";
			$artifact_class->$id->pmid = "";
		}

		$pool = new HttpRequestPool();

        foreach ($config->plugins as $sourceName => $pluginUrl){
			$request = new HttpRequest($pluginUrl, HTTP_METH_POST);
			$request->setPostFields(array('query' => json_encode($artifact_class)));
	  		$pool->attach($request);			
        }		

		$pool->send();
		
		foreach ($pool as $request) {
			$body = $request->getResponseBody();
			if ($body != "") {
	            $pluginResponse = json_decode($body);
				$ts=0;
				$sourceName = $pluginResponse->source_name;
	            $this->updateDoc($collectionId, $sourceName, $pluginResponse, 0, $ts, $couch);
			}
		}
	
	}
		
    /**
     * Saves a collection to the database based on user input
     *
     * @param string $title Collection title
     * @param string $idsStr A list of artifact IDs, delimited by linebreaks\
     * @param string $ts Unix timestamp as a string, useful for testing
     * @return StdClass A CouchDB response object
     */
    public function save($title, $idsStr, $config) {
        // sanitize inputs
        $title = ($title) ? strip_tags($title) : false;

		$idsStr = trim(strip_tags($idsStr));

        // build the object
        $doc = new stdClass();
        $doc->_id = $this->randStr(6);
        $doc->created_at = (string)time();
        $doc->title = $title;
        $doc->artifact_ids = $this->idsFromStr($idsStr);
        $doc->sources = new stdClass(); // we'll fill this later
        $doc->updates = new stdClass(); // also for later

        // put it in couchdb
        $couch = new Couch_Client($config->db->dsn, $config->db->name);
        $response = $couch->storeDoc($doc);
        return $response;
    }

    /**
     * Loads the data on the collection specified by $this->id
     */
    public function fetch($collectionId, $couch){
        try {
            $doc = $couch->getDocRaw($collectionId);
        }
        catch (Exception $e) {
            // throw $e;
            // log the exception
            return false;
        }
        return $doc;
    }
}
?>
