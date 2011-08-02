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
        if (!isset($this->artifactIds)){
            throw new Exception("There are no artifact IDs loaded to send the Source API.");
        }
		
        #Change request to GET with a parameter key for now to help with debugging.  Later change to a POST with param key.
        parent::setParameterGet("query", json_encode($this->artifactIds));
        return parent::request("GET");
    }


}
?>
