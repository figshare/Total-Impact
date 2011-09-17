<?php

#require_once 'FirePHPCore/fb.php';

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
     * Saves a collection to the database based on user input
     *
     * @param string $title Collection title
     * @param string $idsStr A list of artifact IDs, delimited by linebreaks\
     * @return StdClass A CouchDB response object
     */
    public function create($title, $idsStr, $config) {
        // sanitize inputs
        $title = ($title) ? strip_tags($title) : false;

		$idsStr = trim(strip_tags($idsStr));

        // build the object
        $doc = new stdClass();
        $doc->_id = $this->randStr(6);
        $doc->created_at = (string)time();
        $doc->title = $title;
        $doc->artifact_ids = $this->idsFromStr($idsStr);
        $doc->aliases = new stdClass(); // we'll fill this later
        $doc->sources = new stdClass(); // we'll fill this later
        $doc->updates = new stdClass(); // also for later

        // put it in couchdb
        $couch = new Couch_Client($config->db->dsn, $config->db->name);
        $response = $this->robustStoreDoc($doc, 0, $couch);
        return $response;
    }


    /**
     * Stores a given couch doc with information from the plugin
     * Tries again a few times if storage doesn't work (generally that's because
     *    the doc was changed after we downloaded it).
     *
     */
    private function robustStoreDoc($doc, $tries, $couch){
        $maxTries = 5;
        try {
            $response = $couch->storeDoc($doc);
        }
        catch (Exception $e){
            if ($tries > $maxTries) {
                throw $e;
            }
            sleep(0.);
            return $this->robustStoreDoc($doc, $tries+1, $couch);
        }

        return $response;
    }

	/**
	* Queries plugins
	* $type is "sources" or "aliases"
	**/
   public function queryPlugins($doc, $pluginQueryData, $pluginUrls, $pluginType) {
		#FB::log($pluginUrls);
		error_log("*******in queryPlugins", 0);
	
		$pool = new HttpRequestPool();

        foreach ($pluginUrls as $sourceName=>$pluginUrl){
			$request = new HttpRequest($pluginUrl, HTTP_METH_POST);
			$request->setPostFields(array('query' => json_encode($pluginQueryData)));
			$request->setOptions(array('timeout' => 250));
			#FB::log($request);
	  		$pool->attach($request);			
        }		

		try {
			$pool->send();
		} catch (HttpRequestPoolException $e) {
			error_log($e, 0);
		}
		
		foreach ($pool as $request) {
			$body = $request->getResponseBody();
			if ($body != "") {
	            $pluginResponse = json_decode($body);
				if (isset($pluginResponse)) {
					$sourceName = $pluginResponse->source_name;
					#FB::log($sourceName);
					#FB::log($pluginType);
					#FB::log($pluginResponse);
					#FB::log($doc);
					#error_log($sourceName, 0);	
					#error_log(serialize($pluginResponse), 0);	
	       			$doc->$pluginType->$sourceName = $pluginResponse;
					#FB::log($doc);
					#FB::log("hi heather!");
				}
			}
		}
		return $doc;	
   }

	/**
	* Updates the collection by calling plugins and storing the $doc again
	**/
	public function callPlugins($collectionId, $config, $pluginList, $pluginQueryData, $pluginType) {
		#error_log("*******in update", 0);
		$couch = new Couch_Client($config->db->dsn, $config->db->name);
		
		/* load the doc fresh from the DB to prevent conflicts */
		$doc = $couch->getDoc($collectionId);

		#FB::log($pluginQueryData);

		$doc = $this->queryPlugins($doc, $pluginQueryData, $pluginList, $pluginType); 
		$response = $this->robustStoreDoc($doc, 0, $couch);
		return($doc);
	}

	public function getArtifactsIds($collectionId, $config) {
		$couch = new Couch_Client($config->db->dsn, $config->db->name);
		$doc = $couch->getDoc($collectionId);
		$pluginQueryData = new stdClass();
		foreach ($doc->artifact_ids as $index => $id) {
			$pluginQueryData->$id = new stdClass();
		}
		return($pluginQueryData);
	}
	
	/**
	* Updates the collection by calling plugins and storing the $doc again
	**/
	public function update($collectionId, $config) {
		$pluginQueryData = $this->getArtifactsIds($collectionId, $config);
		
		$doc = $this->callPlugins($collectionId, $config, $config->plugins->alias, $pluginQueryData, "aliases");
				
		#FB::log($doc);
		foreach ($doc->aliases as $aliasName => $content) {
			foreach ($content->artifacts as $artifactId => $aliases) {
				foreach ($aliases as $idType => $alias) {
					$pluginQueryData->$artifactId->$idType = $alias;
				}
			}
		}
		
		$doc = $this->callPlugins($collectionId, $config, $config->plugins->source, $pluginQueryData, "sources");
	}

    /**
     * Loads the data on the collection specified by $this->id
     */
    public function fetch($collectionId, $config){
        $couch = new Couch_Client($config->db->dsn, $config->db->name);
	
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
