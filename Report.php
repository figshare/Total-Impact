<?php

/**
 * Description of Report
 *
 * @author jason
 */
class Report {
    private $couch;
    private $id;
    private $data;
    
    function __construct(Couch_Client $couch, $id) {
        $this->couch = $couch;
        $this->id = $id;
    }
    
    
    public function fetch(){
        try {
            $ret = $this->couch->getShow('main', 'by_type', $this->id);
        }
        catch (Exception $e) {
            header('Location: index.php');
        }
        return $this->data = json_decode($ret);
    }

    public function render() {
        foreach ($data->metrics as $artifactTypeName => $artifacts){
            $this->printArtifactType($artifactTypeName, $artifacts);

        }

    }

    private function printArtifactType($name, $artifacts){
            echo "<div class='artifact-type $name>";
            foreach ($artifacts as $id => $artifact){
                $this->printArtifact($id, $artifact);
            }
            echo "</div>";
    }

    private function printArtifact($id, $artifact) {
        echo "<div class='artifact $id>";
        foreach ($artifact as $metricName => $metric) {
            
        }
        echo "</div>";

    }

    private function printMetrics($data){
        
    }
    
    
    
    
}

?>
