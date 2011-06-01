<?php
/**
 * A thin wrapper around Zend_Http_Client to handle communication with a given plugin
 *
 * @author jason
 */
class Plugin extends Zend_Http_Client {
    private $name;
    private $artifactIds;

    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }
    public function setArtifactIds($artifactIds) {
        $this->artifactIds = $artifactIds;
    }




}
?>
