<?php

/**
 * This is a wrapper around the json returned by the by_artifact show from the database.
 * It allows report.php to just call these methods, rather than worrying about the format
 * of what the database returns.
 *
 * @author jason
 */
class Models_Report {
    private $couch;
    private $id;
    private $data;
    
    function __construct(Couch_Client $couch, $id) {
        $this->couch = $couch;
        $this->id = $id;
    }

    public function getBestIdentifier() {
        var_dump($this->data);
        if ($this->data->meta->title) {
            return $this->data->meta->title;
        }
        else {
            return $this->id;
        }
    }

    public function getId() {
        return $this->id;
    }

    /**
     * Gets the date of the collection's creation
     * @param string $format The format for the returned date, as specified with
     *              PHP's date() function (http://php.net/manual/en/function.date.php)
     * @return string The date of the collections creation
     */
    public function getCreatedAt($format) {
        return date($format, $this->data->meta->created_at);
    }

    /**
     * Gets an array of update times for each Source we have data for.
     * @param string $format $format The format for the returned date, as specified with
     *              PHP's date() function (http://php.net/manual/en/function.date.php)
     * @return array An associative array of <SourceName> => <date>
     */
    public function getLastUpdatedAt($format){
        $ret = array();
        foreach ($this->data->meta->updates as $sourceName => $ts){
            $ret[$sourceName] = date($format, $ts);
        }
        return $ret;
    }

    public function getArtifactsCount() {
        return count($this->data->meta->artifact_ids);
    }
    
    public function fetch(){
        try {
            $ret = $this->couch->getShow('main', 'by_artifact', $this->id);
        }
        catch (Exception $e) {
            throw $e;
            // log the exception
            return false;
        }
        $this->data = json_decode($ret);
        return $this->data;
    }

    public function render() {
        if (!count((array)$this->data->metrics)){
            $this->printNothingHereMsg();
        }
        else {
            foreach ($this->data->metrics as $artifactTypeName => $artifacts){
                $this->printArtifactType($artifactTypeName, $artifacts);

            }
        }

    }

    private function printArtifactType($name, $artifacts){
            echo "<div class='artifact-type $name'><h2>$name</h2>";
            echo "<ul>";
            foreach ($artifacts as $id => $artifact){
                $this->printArtifact($id, $artifact);
            }
            echo "</ul></div>";
    }

    private function printArtifact($id, $artifact) {
        echo "<li class='artifact $id'>";
        echo "<h3>$id</h3>"; // here's where we'd print a name/title of the artifact if we had it.
        echo "<dl>";
        foreach ($artifact as $metricName => $metric) {
            echo "<dt><img class='icon' src='{$metric->icon}'><span class='metric-name'>$metricName</span></dt>";
            echo "<dd>{$metric->metric_value}</dd>";
        }
        echo "</dl></li>";

    }

    private function printNothingHereMsg(){
        echo "<p class='nothing-here'>We weren't able to find any nonzero impact metrics
            for this collection. You could try submitting again in the future, since
            impact often grows over time. You can also
            <a href='http://groups.google.com/group/total-impact'>contact us</a>
            and suggest other places to look for impact.</p>";
    }
    
    
    
    
}

?>
