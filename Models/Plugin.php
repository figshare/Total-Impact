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
        parent::setRawData(json_encode($this->artifactIds), 'text/json');
        return parent::request("POST");

    }




}
?>
