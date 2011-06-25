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

    /**
     * Gets the number of artifacts in the *entire* collection (not just the ones we've found metrics for)
     * @return int number of artifacts
     */
    public function getArtifactsCount() {
        return count($this->data->meta->artifact_ids);
    }
    /**
     * @return a JSON-formated object with information about each Source
     */
    private function getAboutSourcesJson(){
        return json_encode($this->data->meta->about_metrics);
    }
    /**
     * Loads the data on the collection specified by $this->id
     */
    public function fetch(){
        try {
            $ret = $this->couch->getShow('main', 'by_artifact', $this->id);
        }
        catch (Exception $e) {
            // throw $e;
            // log the exception
            return false;
        }
        $this->data = json_decode($ret);
        return $this->data;
    }

    public function render() {
        $ret = '';
        $ret .= "<div id='rendered-report'>";
        if (!count((array)$this->data->artifacts)){
            $ret .= $this->printNothingHereMsg();
        }
        else {
            $genres = $this->sortByGenre($this->data->artifacts);
            foreach ($genres as $genreName => $artifacts){
                $ret .= $this->printGenre($genreName, $artifacts);
            }
        }
        $ret .= "</div>";
        return $ret;
    }

    private function printGenre($name, $artifacts){
        $ret = '';
        $ret .= "<div class='genre $name'><h2>$name</h2>";
        $ret .= "<ul>";
        foreach ($artifacts as $id => $artifact){
            $ret .= $this->printArtifact($id, $artifact);
        }
        $ret .=  "</ul></div>";
        return $ret;
    }

    private function printArtifact($id, $artifact) {
        $ret = '';
        $ret .= "<li class='artifact'>";
        $ret .= "<h3>$id</h3>"; // here's where we'd print a name/title of the artifact if we had it.
        $ret .= "<ul class='source-list'>";
        foreach ($artifact as $sourceName => $sourceData) {
            $ret .= $this->printSource($sourceName, $sourceData);
        }
        $ret .= "</ul></li>";
        return $ret;

    }

    private function printSource($sourceName, $sourceData){
        unset($sourceData->type); // user doesn't need to see this
        $faviconImg = '';
        if (isset($this->data->meta->about_sources->$sourceName->icon)){
            if ($this->data->meta->about_sources->$sourceName->icon){
                $url = $this->data->meta->about_sources->$sourceName->icon;
                $faviconImg = "<img src='$url' alt='favicon' />";
            }
        }
        $faviconUrl = $this->data->meta->about_sources->$sourceName->icon;
        $ret = '';
        $ret .= "<li class='source $sourceName'>";
        $ret .= "<h4>$faviconImg$sourceName</h4>";
        $ret .= "<dl>";
        foreach ($sourceData as $metricName => $metricValue){
            $ret .= "<dt>$metricName</dt><dd>$metricValue</dd>";
        }
        $ret .= "</dl></li>";
        return $ret;
    }

    private function printNothingHereMsg(){
        return "<p class='nothing-here'>We weren't able to find any nonzero impact metrics
            for this collection. You could try submitting again in the future, since
            impact often grows over time. You can also
            <a href='http://groups.google.com/group/total-impact'>contact us</a>
            and suggest other places to look for impact.</p>";
    }

    /**
     * Makes an array of artifacts, each listed under its genre (type)
     */
    private function sortByGenre(stdClass $collection){
        $genres = new StdClass;
        foreach ($collection as $id => $sources) {

            // we want to figure the genre, but each Source has its own opinion on that.
            // so we gather them all.
            $reportedGenres = array();
            foreach ($sources as $sourceName => $metrics) {
                $reportedGenres[] = $metrics->type;
            }
            $thisArtifactGenre = $this->selectBestGenre($reportedGenres);
            if (!isset($genres->$thisArtifactGenre)) {
                $genres->$thisArtifactGenre = new StdClass;
            }
            $genres->$thisArtifactGenre->$id = $sources;
        }
        return $genres;

    }

    /**
     * Picks the best genre from a list
     * When an artifact belongs to multiple genres (say, web page and article), we want to
     *  put it in the one that's most useful (generally, that means most specific).
     *
     * @param array $suggestedGenres An array of genres names
     * @return string The selected genre--the best from the list.
     */
    private function selectBestGenre(Array $suggestedGenres){
        if (count($suggestedGenres) == 0) {
            throw new Exception("Asked to pick a genre from an empty suggestion list");
        }
        // if there's only one genre suggested, and it's "NA", return that
        $uniqueGenres = array_flip($suggestedGenres);
        if (count($uniqueGenres) == 1 && isset($uniqueGenres["NA"]) ){
            return "NA";
        }
        else { // just return the first suggestion for now...more sophisticated guessing can be added later.
            return reset($suggestedGenres);
        }
    }

    
    
    
    
}

?>
