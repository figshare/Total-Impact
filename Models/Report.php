<?php

#require_once 'FirePHPCore/fb.php';

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
        if ($this->data->title) {
            return $this->data->title;
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
        return date($format, $this->data->created_at);
    }


    /**
     * Gets the date of the collection's last update
     * @param string $format The format for the returned date, as specified with
     *              PHP's date() function (http://php.net/manual/en/function.date.php)
     * @return string The date of the collections last update
     */
    public function getUpdatedAt($format) {
        $ret = array();	

        foreach ($this->data->sources as $source) {
			$sourceName = $source->source_name;
            $ret[$sourceName] = date($format, $source->last_update);
		}
		$max_updated = min($ret);

		return($max_updated);
    }

    /**
     * Gets the number of artifacts in the *entire* collection (not just the ones we've found metrics for)
     * @return int number of artifacts
     */
    public function getArtifactsCount() {
        return count($this->data->artifact_ids);
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
            $ret = $this->couch->getDocRaw($this->id);
        }
        catch (Exception $e) {
            // throw $e;
            // log the exception
            return false;
        }
        $this->data = $ret;
        return $this->data;
    }

    public function render_about_text() {
		$sources = $this->data->sources;
        $ret = '';
        $abouts = $this->getSourceAbouts($sources);
        foreach ($abouts as $sourceName => $about) {
			$ret .= '<ul type="square" class="body">';
            $icon = $about->icon;
       		$faviconUrl = $about->url;
            $ret .= "<a href='$faviconUrl'><img src='$icon' border=0 alt='favicon' />$sourceName</a> ";
            $ret .= $about->desc;
            $ret .= "<br>";
	       	foreach ($about->metrics as $metricName => $metricDescription){
           		$ret .= "<li>";
				$ret .= "<strong><span class='param'>$metricName</span></strong>: <span class='desc''>$metricDescription</span>";
				$ret .= "</li>";
			}
			$ret .= "</ul>";
        }
		
		return $ret;
    }

    public function render() {
		$sources = $this->data->sources;
        $ret = '';
        $ret .= "<div id='rendered-report'>";

		#FB::log($sources);

		/* if no artifacts have metrics, add call here to printNothingHereMsg() */
        $genres = $this->sortByGenre($sources);
		#FB::log($genres);
        $abouts = $this->getSourceAbouts($sources);
		#FB::log($abouts);
        foreach ($genres as $genreName => $artifacts){
			#FB::log($genreName);
            $ret .= $this->printGenre($genreName, $artifacts, $abouts);
        }

        $ret .= "</div>";
        return $ret;
    }

    public function render_as_plain_text() {
		$sources = $this->data->sources;
		$ret_string = '';
		
		/* if no artifacts have metrics, add call here to printNothingHereMsg() */
        $genres = $this->sortByGenre($sources);

        foreach ($genres as $genreName => $artifacts){
			$ret_string .= "<h1>$genreName</h1>";
	
	        foreach ($artifacts as $id => $artifact){
		        foreach ($artifact as $sourceName => $sourceData) {
					if ($sourceName=="CrossRef") {
			           	$biblio = "$sourceData->authors, $sourceData->title, $sourceData->year, $sourceData->journal, $sourceData->doi, PMID:$sourceData->pmid, $sourceData->url";
					} elseif ($sourceName=="Slideshare") {
			           	$biblio = "$sourceData->title; (uploaded in $sourceData->upload_year) $id";
					} elseif ($sourceName=="Dryad") {
			           	$biblio = "$sourceData->authors ($sourceData->year) $sourceData->title, Dryad Data Repository. $id";
					}
					$metrics = '';
			       	foreach ($sourceData as $metricName => $metricValue){
						if (!in_array($metricName, array("authors", "url", "title", "year", "journal", "doi", "pmid", "upload_year", "type"))) {
			           		$metrics .= "$sourceName" . "_" . "$metricName: $metricValue;  ";					
						}
					}
		        }
				$ret_string .= "$id<br/>$biblio<br/>$metrics<br/><p>";
	        }
        }
        #return(json_encode("hello"));

        return(json_encode($ret_string));
    }

    private function printGenre($name, $artifacts, $abouts){
        $ret = '';
        $ret .= "<div class='genre $name'><h2>$name</h2>";
        $ret .= "<ul>";
        foreach ($artifacts as $id => $artifact){
            $ret .= $this->printArtifact($id, $artifact, $abouts);
        }
        $ret .=  "</ul></div>";
        return $ret;
    }

    private function printArtifact($id, $artifact, $abouts) {
        $ret = '';
        $ret .= "<li class='artifact'>";
        $ret .= "<h3>$id</h3>"; // here's where we'd print a name/title of the artifact if we had it.
        $ret .= "<ul class='source-list'>";
        foreach ($artifact as $sourceName => $sourceData) {
            $ret .= $this->printSource($id, $sourceName, $sourceData, $abouts);
        }
        $ret .= "</ul></li>";
        return $ret;

    }

    private function printSource($id, $sourceName, $sourceData, $abouts){
        $faviconImg = '';
        if (isset($abouts->$sourceName->icon)){
            if ($abouts->$sourceName->icon){
                $icon = $abouts->$sourceName->icon;
        		$faviconUrl = $abouts->$sourceName->url;
                $faviconImg = "<a href='$faviconUrl'><img src='$icon' border=0 alt='favicon' /></a>";
            }
        }
        $ret = '';
        $ret .= "<li class='source $sourceName'>";
        $ret .= "<h4>$faviconImg$sourceName</h4>";
		if ($sourceName=="CrossRef") {
           	$ret .= "$sourceData->authors, <a href='$sourceData->url'>$sourceData->title</a>, $sourceData->year, $sourceData->journal, $sourceData->doi, PMID:$sourceData->pmid";
		} elseif ($sourceName=="Slideshare") {
           	$ret .= "<a href='$id'>$sourceData->title</a>; Uploaded in $sourceData->upload_year";
		} elseif ($sourceName=="Dryad") {
           	$ret .= "$sourceData->authors ($sourceData->year) <a href='$id'>$sourceData->title</a>, Dryad Data Repository. $id";
		}
        $ret .= "<p>";
       	foreach ($sourceData as $metricName => $metricValue){
			if (!in_array($metricName, array("authors", "url", "title", "year", "journal", "doi", "pmid", "upload_year", "type"))) {
           		$ret .= "$metricName: $metricValue;\t";					
			}
		}
		$ret .= "</li>";
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
     * Gets all the source about information in one place
     */
    private function getSourceAbouts(stdClass $sources){
        $abouts = new StdClass;
        foreach ($sources as $source) {
			if (isset($source->source_name)) {
				$sourceName = $source->source_name;
            	$abouts->$sourceName = $source->about;
			}
        }
        return $abouts;
    }

    /**
     * Makes an array of artifacts, each listed under its genre (type)
     */
    private function sortByGenre(stdClass $sources){
        $genres = new StdClass;

        foreach ($sources as $source) {
			$sourceName = $source->source_name;

            // we want to figure the genre, but each Source has its own opinion on that.
            // so we gather them all.
            foreach ($source->artifacts as $id=>$item) {
                $thisArtifactGenre = $item->type;
	            if (!isset($genres->$thisArtifactGenre)) {
	                $genres->$thisArtifactGenre = new StdClass;
	            }
	            if (!isset($genres->$thisArtifactGenre->$id)) {
	                $genres->$thisArtifactGenre->$id = new StdClass;
	            }
				$genres->$thisArtifactGenre->$id->$sourceName = $item;
            }
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
