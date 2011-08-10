<?php

/**
 * A thin wrapper around Zend_Http_Client to handle communication with a given plugin
 *
 * @author jason
 */
class Models_Plugin extends Zend_Http_Client {
    private $name;
    private $artifactIds;

    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }

    public function setArtifactIds(stdClass $artifactIds) {
        $this->artifactIds = $artifactIds;
    }
    public function fetchData(){
		#FB::log("in fetchData");
        if (!isset($this->artifactIds)){
			#FB::log("no artifactIds");
            throw new Exception("There are no artifact IDs loaded to send the Source API.");
        }

		#FB::log($this);
		
        parent::setParameterPost("query", json_encode($this->artifactIds));
		#FB::log(json_encode($this->artifactIds));
		#FB::log(json_encode(" after set timeout"));		
        return parent::request("POST");
    }


}
?>
