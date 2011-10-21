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
function sortByValue($a, $b) {
	  if ($a->metricValue == $b->metricValue) {
	    return 0;
	  } else {
	    return $a->metricValue < $b->metricValue ? 1 : -1; // reverse order
	  }
	}

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
		$max_updated = max($ret);

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
			$ret .= '<ul type="square" class="about $sourceName">';
       		$Url = $about->url;
            $ret .= "<a href='$Url'>$sourceName</a>";
            $ret .= " <span class='about sourceDescription $sourceName'>" . $about->desc . "</span>";
            $ret .= "<br>";
	       	foreach ($about->metrics as $metricName => $metricDescription){
				$Img = $this->getMetricImage($sourceName, $metricName, $abouts);
           		$ret .= "<li>";
				$prettyMetricName = str_replace("_", " ", $metricName);
				$ret .= "$Img <strong><span class='about metricName $sourceName $metricName '>$prettyMetricName</span></strong>: <span class='about metricDescription $sourceName $metricName '>$metricDescription</span>";
				$ret .= "</li>";
			}
			$ret .= "</ul>";
        }
		
		return $ret;
    }

    public function render($showZeros=True) {
		$sources = $this->data->sources;
        $ret = '';
        $ret .= "<div id='rendered-report'>";

		#FB::log($sources);

		if (isset($this->data->status->user_alert_artifact_ids_truncated)) {
			$ret .= "<p>Article ID list was truncated to maximum length: 250 artifacts.</p>"; 
		}

		/* if no artifacts have metrics, add call here to printNothingHereMsg() */
        $genres = $this->sortByGenre($sources);
		#FB::log($genres);
        $abouts = $this->getSourceAbouts($sources);
		#FB::log($abouts);
        foreach ($genres as $genreName => $artifacts){
			#FB::log($genreName);
            $ret .= $this->printGenre($genreName, $artifacts, $abouts, $showZeros);
        }

        $ret .= "</div>";

        return $ret;
    }

	public function render_status_of_metrics($sources) {
		$ret_string = "";
        foreach ($sources as $sourceName => $sourceData) {
			$ret_string .= '<tr><td>';
           	$ret_string .= $sourceName;
			$ret_string .= '</td><td>';
			$statuses = $sourceData->status;
	       	foreach ($statuses as $statusName => $statusValue){
	           	$ret_string .= "<b>$statusName:</b>" . " $statusValue<br/>";					
			}
           	$ret_string .= "</td></tr>";
        }			
		return $ret_string;
	}

    public function render_status() {
		$ret_string = "";
		$ret_string .= "<b>Collection status: </b><br/>";
       	foreach ($this->data->status as $statusName => $statusValue){
           	$ret_string .= "<b>$statusName:</b>" . " $statusValue<br/>";					
		}
		$ret_string .= " <b>Input ids:</b></br>";
		$ids = $this->data->artifact_ids;
		sort($ids);
		foreach ($ids as $id) {
			$ret_string .= "$id</br>";
		}
		$ret_string .= '<table border="1">';
	
		$ret_string .= $this->render_status_of_metrics($this->data->aliases);
		$ret_string .= $this->render_status_of_metrics($this->data->sources);
		
        $ret_string .= "</table>";
		#breadcrumb($ret_string, 0);
        return($ret_string);
    }

    public function render_as_list() {
		
		$sources = $this->data->sources;
		$ret_string = '';
		
        $genres = $this->sortByGenre($sources);
        foreach ($genres as $genreName => $artifacts){
	        foreach ($artifacts as $id => $artifact){
				$metrics = '';
		        foreach ($artifact as $sourceName => $sourceData) {
			       	foreach ($sourceData as $metricName => $metricValue){
			           		$metrics .= "$id|$genreName|$sourceName" . "_" . "$metricName|$metricValue<br/>";					
					}
		        }		
				$ret_string .= "$metrics";
	        }
        }
		#FB::log($ret_string);
        return($ret_string);
    }

    private function printGenre($name, $artifacts, $abouts, $showZeros){
        $ret = '';
        $ret .= "<div class='genre $name'><h2>$name</h2>";
        $ret .= "<ul>";
        foreach ($artifacts as $id => $artifact){
            $ret .= $this->printArtifact($id, $artifact, $abouts, $showZeros);
        }
        $ret .=  "</ul></div>";
        return $ret;
    }
	
    private function printArtifact($id, $artifact, $abouts, $showZeros) {
        $ret = '';
        $ret .= "<li class='artifact'>";
        $ret .= "<h3>$id</h3>"; // here's where we'd print a name/title of the artifact if we had it.
        $ret .= "<ul class='source-list'>";

		$biblioSources = array("CrossRef", "PubMed", "Mendeley");
		$biblioSource = "";
		foreach ($biblioSources as $candidateSource)	{
			if (isset($artifact->$candidateSource)) {
				if (isset($artifact->$candidateSource->title)) {
					if (strlen($artifact->$candidateSource->title) > 0 ) {
						$biblioSource = $candidateSource;
						break;
					}
				}
			}
		}
        foreach ($artifact as $sourceName => $sourceData) {
            $ret .= $this->printBiblio($id, $sourceName, $sourceData, $abouts, $biblioSource, $showZeros);
        }
		
        $ret .= $this->printArtifactMetrics($artifact, $abouts, $showZeros);
        $ret .= "</ul></li>";
        return $ret;

    }

	private function getMetricImage($sourceName, $metricName, $abouts) {
		$Img = "";
        if (isset($abouts->$sourceName->icon)){
            if ($abouts->$sourceName->icon){
				if (isset($abouts->$sourceName->icon->$metricName)) {
	                $icon = $abouts->$sourceName->icon->$metricName;						
				} else {
	                $icon = $abouts->$sourceName->icon;
				}
        		$Url = $abouts->$sourceName->url;
        		$Img = "<span class='metric-image'><a href='$Url'><img src='$icon' width='16'' height='16' border=0 alt='' /></a></span>";					
			}
		}
		return($Img);
	}

	private function getTooltipText($sourceName, $metricName, $abouts) {
		$prettyMetricName = str_replace("_", " ", $metricName);
		$tooltiptext = $sourceName . " " . $prettyMetricName . ": " . $abouts->$sourceName->metrics->$metricName;
		return($tooltiptext);
	}
	
	private function printArtifactMetrics($artifact, $abouts, $showZeros) {
		# First check to see if will render any metrics.  If not, don't show the sourceData.
		$metrics_array = array();

        foreach ($artifact as $sourceName => $sourceData) {
						
	       	foreach ($sourceData as $metricName => $metricValue) {
				$metrics_ret = "";

				if ($showZeros or ($metricValue != 0)) {
					if (!in_array($metricName, array("authors", "url", "title", "year", "journal", "doi", "pmid", "upload_year", "type"))) {
						$prettyMetricName = str_replace("_", " ", $metricName);

						$Img = $this->getMetricImage($sourceName, $metricName, $abouts);
						$tooltiptext = $this->getTooltipText($sourceName, $metricName, $abouts);
						$prettyMetricName = str_replace("_", " ", $metricName);

						#FB::log($tooltiptext);
						$metrics_ret .= "<div class='metrics-div'>";
						if (isset($sourceData->show_details_url)) {
	           				$metrics_ret .= "<a target='_blank' href='$sourceData->show_details_url'><span class='metric-value'>$metricValue</span></a>$Img<span class='metric-name' title='$tooltiptext'>$prettyMetricName</span> \t";					
						} else {
	           				$metrics_ret .= "<span class='metric-value'>$metricValue</span>$Img<span class='metric-name' title='$tooltiptext'>$prettyMetricName</span> \t";					
						}
						$metrics_ret .= "</div>";
					}
				}
				$metrics_array[$metrics_ret] = $metricValue;
			}
		}
		arsort($metrics_array);
		$return = implode("" ,array_keys($metrics_array));
		#FB::log($return);
		return($return);
	}

    private function printBiblio($id, $sourceName, $sourceData, $abouts, $biblioSource, $showZeros) {
        $ret = "<div class='biblio $sourceName'>";

		$title = "<span class='title'>$sourceData->title</span>";
		if ($sourceName=="CrossRef" and $biblioSource=="CrossRef") {
			$authors = "<span class='meta-author'>$sourceData->authors</span>";
			$year = "<span class='meta-year'>($sourceData->year) </span>";
			$journal = "<span class='meta-repo'>$sourceData->journal.</span>";
			$doi = "<span class='meta-doi'> http://dx.doi.org/$sourceData->doi</span>";
			$url = "http://dx.doi.org/$sourceData->doi";
           	$ret .= "$authors $year <a class='meta-url' target='_blank' href='$url'> $title</a> $journal $doi<br/>";
		} elseif ($sourceName=="PubMed" and $biblioSource=="PubMed") {
			$authors = "<span class='meta-author'>$sourceData->authors</span>";
			$year = "<span class='meta-year'>($sourceData->year) </span>";
			$journal = "<span class='meta-repo'>$sourceData->journal.</span>";
			$url = "http://www.ncbi.nlm.nih.gov/pubmed/$sourceData->pmid";
			$pmid = "<span class='meta-pmid'>$sourceData->pmid</span>";
           	$ret .= "$authors $year <a class='meta-url' target='_blank' href='$url'> $title</a> $journal $pmid<br/>";
		} elseif ($sourceName=="Mendeley" and $biblioSource=="Mendeley") {
			$authors = "<span class='meta-author'>$sourceData->authors</span>";
			$year = "<span class='meta-year'>($sourceData->year) </span>";
			$journal = "<span class='meta-repo'>$sourceData->journal.</span>";
			$url = $sourceData->url;
           	$ret .= "$authors $year <a class='meta-url' target='_blank' href='$url'> $title</a> $journal<br/>";
		} 
		if ($sourceName=="Slideshare") {
           	$ret .= "<a class='meta-doi' href='$id'>$sourceData->title</a>; Uploaded in $sourceData->upload_year<br/>";
		} elseif ($sourceName=="FigShare") {
			$repo = "<span class='meta-repo'>FigShare.</span>";
           	$ret .= "<a class='meta-doi' href='$id'>$sourceData->title</a>, $repo $id<br/>";
		} elseif ($sourceName=="Dryad") {
			$authors = "<span class='meta-author'>$sourceData->authors</span>";
			$repo = "<span class='meta-repo'>Dryad Data Repository.</span>";
			$url = "http://dx.doi.org/$sourceData->doi";
			$doi = "<span class='meta-doi'> http://dx.doi.org/$sourceData->doi</span>";
           	$ret .= "$authors $year <a class='meta-url' target='_blank' href='$url'> $title</a> $repo $doi<br/>";
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
