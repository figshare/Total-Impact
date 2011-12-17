<?php

#require_once 'FirePHPCore/fb.php';

/**
 * Description of Collection
 *
 * @author jason
 */
class Models_Collection {
	static $MAX_NUM_ARTIFACTS = 250;
        private $couch;

        function __construct(Couch_Client $couch) {
            $this->couch = $couch;
        }

        /**
    * Splits a string into lines
    *
    * @param String $str
    * @return Array the lines from the string
    */
   private function idsFromStr($str){
		if (strlen($str) > 0) {
        	$lines = preg_split("/[\s,]+/", $str);
		} else {
			$lines = array();
		}
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

	/* this will eventually replace create */
    public function build($input, $version) {
        // build the object
        $doc = new stdClass();
        $doc->_id = $this->randStr(6);
        $doc->id = $doc->_id;
        $doc->title = ($input->title) ? strip_tags($input->title) : false;
        $doc->description = $input->description;
        $doc->created_at = (string)time();
        $doc->last_updated_at = "";
        $doc->created_by = $input->created_by;
        $doc->item_ids = $this->idsFromStr(trim(strip_tags($input->item_ids)));
        $doc->version = Collections::$VERSION;

        $doc->status = new stdClass(); // also for later
		
		if (count($doc->artifact_ids) > Collection::$MAX_NUM_ARTIFACTS) {
			$doc->artifact_ids = array_slice($doc->artifact_ids, 0, $maxNumArtifacts);
			$doc->status->user_alert_artifact_ids_truncated = "True";
		}
		
		return($doc);
    }
		
    /**
     * Saves a collection to the database based on user input
     *
     * @param string $title Collection title
     * @param string $idsStr A list of artifact IDs, delimited by linebreaks\
     * @return StdClass A CouchDB response object
     */
    public function create($title, $idsStr) {
		$maxNumArtifacts = 250;
		
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
        $doc->status = new stdClass(); // also for later
        $doc->last_updated_at = (string)time();

		if (count($doc->artifact_ids) > $maxNumArtifacts) {
			$doc->artifact_ids = array_slice($doc->artifact_ids, 0, $maxNumArtifacts);
			$doc->status->user_alert_artifact_ids_truncated = "True";
		}

        // put it in couchdb
        $response = $this->robustStoreDoc($doc);
        return $response;
    }


    /**
     * Stores a given couch doc with information from the plugin
     * Tries again a few times if storage doesn't work (generally that's because
     *    the doc was changed after we downloaded it).
     *
     */
    private function robustStoreDoc($doc, $tries=0){
        $maxTries = 5;
        try {
            $response = $this->couch->storeDoc($doc);
        }
        catch (Exception $e){
            if ($tries > $maxTries) {
                throw $e;
            }
            sleep(0.);
            return $this->robustStoreDoc($doc, $tries+1);
        }

        return $response;
    }

	/**
	* Queries plugins
	* $type is "sources" or "aliases"
	**/
   public function queryPlugins($doc, $pluginQueryData, $pluginUrls, $pluginType) {
		#FB::log($pluginUrls);
		breadcrumb("*******in queryPlugins", 0);
	
		$pool = new HttpRequestPool();

        foreach ($pluginUrls as $sourceName=>$pluginUrl){
			$request = new HttpRequest($pluginUrl, HTTP_METH_POST);
			$encoded_data = json_encode($pluginQueryData);
			$doc->status->encoded_data = $encoded_data;
			$request->setPostFields(array('query' => $encoded_data));
			$request->setOptions(array('timeout' => 250));
			#FB::log($request);
	  		$pool->attach($request);			
        }		

		try {
			$pool->send();
			$doc->status->HttpRequestPoolException = "Plugin query success";
		} catch (HttpRequestPoolException $e) {
			$doc->status->HttpRequestPoolException = "HttpRequestPoolException:" . $e;
			breadcrumb($e, 0);
		}
		
		foreach ($pool as $request) {
			$body = $request->getResponseBody();
			if ($body != "") {
	            $pluginResponse = json_decode($body);
				if (isset($pluginResponse)) {
					$sourceName = $pluginResponse->source_name;
	       			$doc->$pluginType->$sourceName = $pluginResponse;
					breadcrumb("Got response from " . $sourceName);
				}
			}
		}
		return $doc;	
   }

	/**
	* Updates the collection by calling plugins and storing the $doc again
	**/
	public function callPluginsAndStoreDoc($collectionId, $pluginList, $pluginQueryData, $pluginType) {
		#breadcrumb("*******in update", 0);
		
		/* load the doc fresh from the DB to prevent conflicts */
		$doc = $this->couch->getDoc($collectionId);

		$doc = $this->queryPlugins($doc, $pluginQueryData, $pluginList, $pluginType); 
		$response = $this->robustStoreDoc($doc);
		return($doc);
	}

	public function getArtifactsIds($collectionId) {
		$doc = $this->couch->getDoc($collectionId);
		$pluginQueryData = new stdClass();
		if (isset($doc->artifact_ids)) {
			foreach ($doc->artifact_ids as $index => $id) {
				$pluginQueryData->$id = new stdClass();
			}
		}
		return($pluginQueryData);
	}
	
	public function consolidateAliases($pluginQueryDataInitial, $doc) {
		$pluginQueryDataResponse = $pluginQueryDataInitial;
		foreach ($doc->aliases as $aliasName => $content) {
			foreach ($content->artifacts as $artifactId => $aliases) {
				foreach ($aliases as $idType => $alias) {
					$pluginQueryDataResponse->$artifactId->$idType = $alias;
				}
			}
		}	
		return($pluginQueryDataResponse);	
	}
	
	/**
	* Updates the collection by calling plugins and storing the $doc again
	**/
	public function update($collectionId, Zend_Config_Ini $config) {
		#get initial list
		$pluginQueryData = $this->getArtifactsIds($collectionId);
		
		# call alias plugins sequentially
		$pluginUrls = $config->plugins->alias;
		foreach ($pluginUrls as $sourceName=>$pluginUrl) {
			$doc = $this->callPluginsAndStoreDoc($collectionId, array($pluginUrl), $pluginQueryData, "aliases");
			$pluginQueryData = $this->consolidateAliases($pluginQueryData, $doc);		
		}
			
		#$pluginQueryData = $this->consolidateAliases($pluginQueryData, $doc);		
		#FB::log($doc);
		
		# call metrics plugins in parallel
                $doc->last_updated_at = (string)time();
		$doc = $this->callPluginsAndStoreDoc($collectionId, $config->plugins->source, $pluginQueryData, "sources");
	}

    /**
     * Loads the data on the collection specified by $this->id
     */
    public function fetch($collectionId,  $config=NULL){
		if (isnull($config)) {
      		$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
		}

        try {
            $doc = $this->couch->getDocRaw($collectionId);
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
