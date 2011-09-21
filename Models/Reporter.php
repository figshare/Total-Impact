<?php

#require_once 'FirePHPCore/fb.php';
#ob_start();

/**
 * This is a wrapper around the json returned by the by_artifact show from the database.
 * It allows report.php to just call these methods, rather than worrying about the format
 * of what the database returns.
 *
 * @author jason
 */
class Models_Reporter {
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
       		$Url = $about->url;
            $ret .= "<a href='$Url'><img src='$icon' border=0 alt='' />$sourceName</a> ";
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
		#FB::log("render as plain text");
		
		$sources = $this->data->sources;
		$ret_string = '';
		
		/* if no artifacts have metrics, add call here to printNothingHereMsg() */
        $genres = $this->sortByGenre($sources);
		
        foreach ($genres as $genreName => $artifacts){
			#FB::log($genreName);
	
			$ret_string .= "<h1>$genreName</h1>";
	
	        foreach ($artifacts as $id => $artifact){
				$biblio = "";
				$metrics = '';
		        foreach ($artifact as $sourceName => $sourceData) {
					if ($sourceName=="CrossRef") {
			           	#$biblio = "$sourceData->authors ($sourceData->year) $sourceData->title. $sourceData->journal. $sourceData->doi, PMID:$sourceData->pmid, $sourceData->url";
			           	$biblio .= "$sourceData->authors ($sourceData->year) $sourceData->title. $sourceData->journal.";
					} elseif ($sourceName=="Slideshare") {
			           	$biblio .= "$sourceData->title; (uploaded in $sourceData->upload_year) $id";
					} elseif ($sourceName=="Dryad" and $genreName=="dataset") {
			           	$biblio .= "$sourceData->authors ($sourceData->year) $sourceData->title, Dryad Data Repository. $id";
					} else {
						$biblio .= "";
					}
			       	foreach ($sourceData as $metricName => $metricValue){
						if (!in_array($metricName, array("authors", "url", "title", "year", "journal", "doi", "pmid", "upload_year", "type"))) {
			           		$metrics .= "$sourceName" . "_" . "$metricName: $metricValue;  ";					
						}
					}
		        }		
				$ret_string .= "$id<br/>$biblio<br/>$metrics<br/><p>";
	        }
        }

        return(json_encode($ret_string));
    }

    public function render_as_csv() {
		
		$sources = $this->data->sources;
		$ret_string = '';
		
		/* if no artifacts have metrics, add call here to printNothingHereMsg() */
        $genres = $this->sortByGenre($sources);
        foreach ($genres as $genreName => $artifacts){
	        foreach ($artifacts as $id => $artifact){
				$metrics = '';
		        foreach ($artifact as $sourceName => $sourceData) {
			       	foreach ($sourceData as $metricName => $metricValue){
			           		$metrics .= "$id|$genreName|$sourceName" . "_" . "$metricName|$metricValue<p>";					
					}
		        }		
				$ret_string .= "$metrics";
	        }
        }
		#FB::log($ret_string);
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
        $Img = '';
        if (isset($abouts->$sourceName->icon)){
            if ($abouts->$sourceName->icon){
                $icon = $abouts->$sourceName->icon;
        		$Url = $abouts->$sourceName->url;
                $Img = "<a href='$Url'><img src='$icon' border=0 alt='' /></a>";
            }
        }
        $ret = '';
        #$ret .= "<li class='source $sourceName'>";
        #$ret .= "<h4>$Img$sourceName</h4>";
        $ret .= "<div class='source $sourceName'>";
        $ret .= "<p>$Img";
		if ($sourceName=="CrossRef") {
           	#$ret .= "$sourceData->authors ($sourceData->year) <a href='$sourceData->url'>$sourceData->title</a>. <em>$sourceData->journal.</em> $sourceData->doi, PMID:$sourceData->pmid";
           	$ret .= "$sourceData->authors ($sourceData->year) <a href='http://dx.doi.org/$sourceData->doi'>$sourceData->title</a>  <em>$sourceData->journal.</em>";
		} elseif ($sourceName=="Slideshare") {
           	$ret .= "<a href='$id'>$sourceData->title</a>; Uploaded in $sourceData->upload_year";
		} elseif ($sourceName=="FigShare") {
           	$ret .= "<a href='$id'>$sourceData->title</a><br/>";
		} elseif ($sourceName=="Dryad") {
           	$ret .= "$sourceData->authors ($sourceData->year) <a href='$id'>$sourceData->title</a>, <em>Dryad Data Repository.</em> $id<br/>";
		}
        #$ret .= "<p>";
       	foreach ($sourceData as $metricName => $metricValue){
			if (!in_array($metricName, array("authors", "url", "title", "year", "journal", "doi", "pmid", "upload_year", "type"))) {
           		$ret .= "$metricName: $metricValue;\t";					
			}
		}
		$ret .= "</div>";
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
     * Makes an array of artifacts to genre decisions
     */
    private function getBestGenre(stdClass $sources, $lookup_id){
        $list_of_all = array();
        foreach ($sources as $source) {
			$sourceName = $source->source_name;
            // we want to figure the genre, but each Source has its own opinion on that.
            // so we gather them all.
            foreach ($source->artifacts as $id=>$item) {
				if (isset($source->artifacts->$lookup_id->type)) {
               		$list_of_all[$sourceName] = $source->artifacts->$lookup_id->type;
				}	
            }
        }
		#FB::log($list_of_all);
		$flipped = array_flip($list_of_all);
		#FB::log($flipped);
		if (array_key_exists("article", $flipped)) {
			$genre = "article";
		} else {
			# set to first as backup plan
			$genre = reset($list_of_all);
			# now iter and get the first that isn't "unknown" if there is one
			foreach ($list_of_all as $candidate) {
				if ($candidate != "unknown") {
					$genre = $candidate;
					break;
				}
			}
		}
		if (!isset($genre)) {
			$genre = "unknown";
		}
		#FB::log($genre);
        return $genre;
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
                #$thisArtifactGenre = $item->type;
                $thisArtifactGenre = $this->getBestGenre($sources, $id);
	            if (!isset($genres->$thisArtifactGenre)) {
	                $genres->$thisArtifactGenre = new StdClass;
	            }
	            if (!isset($genres->$thisArtifactGenre->$id)) {
	                $genres->$thisArtifactGenre->$id = new StdClass;
	            }
				$genres->$thisArtifactGenre->$id->$sourceName = $item;
            }
        }
		#FB::log($genres);

        return $genres;

    }
    
}

?>
