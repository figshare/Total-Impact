<?php
include 'plugins/common/Metric.php';

/**
 * Class Artifact.php
 *
 */
class Artifact {
    public $metrics = array(); 
    public function addMetric($metric){
        $this->metrics[$metric->metric_name] = $metric;
    }

    public function getList() {
        return $this->metrics; 
    }
    public function getMetric($metric_name) {
        return $this->metrics[$metric_name]; 
    }

}


/**
 * Class ArtifactList.php
 *
 */

class ArtifactList {
    public $list = array();
    public function addArtifact($id,$artifact) {
        $this->list[$id] = $artifact;
    }

    public function getList() {
        return $this->list; 
    }
    public function getArtifact($id) {
        return $this->list[$id]; 
    }
}


/** 
 * ArtifactGroup.php
 *
 */
class ArtifactGroup {
    public list = array();
    public function addGroup($type,$artifacList) {
        $this->list[$type] = $artifactList;
    }

    public function getList() {
        return $this->list; 
    }
    public function getArtifactList($type) {
        return $this->list[$type]; 
    }
}

?>
