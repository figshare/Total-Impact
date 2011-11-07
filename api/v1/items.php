<?php

require_once '../../bootstrap.php'; 
require_once '../../Models/Item.php';

class Items {
	function index($idsString="", $fields=null) {
		if ($idsString==="") {
			throw new RestException(501, "Working on it!");
		}
		
		$ids = explode(',', urldecode($idsString));
		if (isset($fields)) {
			$showAllFields = false;
			$fieldList = explode(',', $fields);
		} else {
			$showAllFields = true;
		}
		
		$result = new stdClass();
		#$result->items = new stdClass();
		# $result->items->item = array(); To give nice xml

		$result->items = array();
		
		foreach ($ids as $id) {
			$item = new stdClass();

			$item->id = $id;
			
			if ($showAllFields or in_array("biblio", $fieldList)) {
				$itemsClass = new Models_Item();
				$aliasResponse = $itemsClass->getAliases($id);
				$biblioResponse = $itemsClass->getBiblio($id, $aliasResponse);
				$item->biblio = $biblioResponse->$id;
			}
			if ($showAllFields or in_array("status", $fieldList)) {
				$item->status = new Status();
			}
			if ($showAllFields or in_array("debug", $fieldList)) {
				$item->debug = new stdClass();
			}
			if ($showAllFields or in_array("metrics", $fieldList)) {
				$itemsClass = new Models_Item();
				$aliasResponse = $itemsClass->getAliases($id);
				$metricsResponse = $itemsClass->getMetrics($id, $aliasResponse);
				$item->metrics = $metricsResponse->$id;
			}
			if ($showAllFields or in_array("aliases", $fieldList)) {
				$itemsClass = new Models_Item();
				$aliasResponse = $itemsClass->getAliases($id);
				$item->aliases = $aliasResponse->$id;
			}
			$result->items[] = $item;
		}
		return $result;
	}
}

class Biblio {
	public $id;
	public $title;
	public $repository;
	public $url;
	public $genre;

    function __construct() {
        $this->id = "unknown";
        $this->title = "unknown";
        $this->repostitory = "";
        $this->url = "";
        $this->genre = "unknown";
    }

}

class Status {
	public $last_updated;
	public $providers_queried;
	public $synonyms_queried;
	public $created_by;
	
    function __construct() {
        $this->created_by = "unknown";
        $this->synonyms_queried = "unknown";
        $this->synonyms_queried = "unknown";
        $this->last_updated = (string)time();
    }
}

class Metric {
	public $id;
	public $meta;
	public $value;
	public $last_updated;
	public $drill_down_url;
}
	