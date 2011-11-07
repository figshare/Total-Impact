<?php

require_once '../../library/restler/restler.php';
require_once '../../Models/Collection.php';

class Collections {
	static $FIELDS = array("id", "title", "description", "created_by", "item_ids");
	static $maxNumArtifacts = 250;
	static $VERSION = "0.1.0";

	function index() {
		throw new RestException(501, "Working on it!");
		return 42;
	}

/*
	// Not tested, needs work
	function get($id=NULL) {
		if (is_null($id)) {
			throw new RestException(501, "Working on it!");			
		} 
		$collection = new Models_Collection();
		$doc = $collection->fetch($id);
		
		return($doc);
	}
		
	// Not tested, needs work
	function post($request_data=NULL, $action) {
		if ($action==="create") {
			$request_data_clean = _validate($request_data);

	        // build the object then store it in DB
			$collection = new Models_Collection();
			$doc = $collection->build($request_data_clean, Collections::$VERSION);
			$collection->store($doc);
			$doc = $collection->update($id);
		} elseif ($action=="update") {
			$collection = new Models_Collection();
			$doc = $collection->update($id);
		}
		return($doc);
	}
	
	// Not tested, needs work
	private function _validate($input){
		$validated = array();
		foreach (Author::$FIELDS as $field) {
			//you may also vaildate the data here
			if (!isset($data[$field])) {
				throw new RestException(417, "$field field missing");
			}
			$validated[$field] = $data[$input];
		}
		return $validated;
	}
	*/	
}
