<?php

class Models_Item {


    protected $doc;
    protected $aliases;
    protected $couch;

    function __construct(Models_Aliases $aliases, Couch_Client $couch) {
        $this->aliases = $aliases;
        $this->namespace = $namespace;
        $this->couch = $couch;
    }

    public function getDoc() {
        return $this->doc;
    }

    public function retrieve() {
        $alias = $this->aliases->getBestAlias(true);
        $result = $this->couch
                ->include_docs(true)
                -limit(1)
                ->key($alias)
                ->getView("main", "by_name");

        if ($result->rows) {
            $this->doc = $result->rows[0]->doc;
            return true;
        }
        else {
            return false;
        }
    }
    
    public function update(Array $providers) {
        if (!$this->doc) {
            $this->retrieve();
        }
        if (!$this->doc) {
            throw new Exception("This item needs to be created before it can be updated.");
        }
        $this->fetchAliases($providers);
        $this->fetchMetrics();
        $this->store();
    }

    public function createDoc() {
        $doc = new stdClass();
        $doc->_id = md5(mt_rand());
        $doc->type = "item";
        $doc->created_at = date("c"); // current time in ISO 8601 format
        $doc->metrics = new stdClass();
        $doc->aliases = (object)$this->aliases;
        $this->doc = $doc;
    }

    private function store() {
        $this->couch->storeDoc($this->doc);
    }
    
    private function fetchMetrics() {
        
    }

    private function fetchAliases(Array $providers) {
        if (!count($providers)) {
            throw new Exception("didn't get any providers to use for the update");
        }
        foreach ($providers as $provider) {
            $this->aliases = $provider->fetchAliases($this->aliases);
            $this->doc->aliases = $this->aliases; // smells bad...
        }
    }




    public function consolidateAliases($pluginQueryDataInitial, $doc) {
        $pluginQueryDataResponse = $pluginQueryDataInitial;
        foreach ($doc->aliases as $aliasName => $content) {
            foreach ($content->artifacts as $artifactId => $aliases) {
                $pluginQueryDataResponse->$artifactId = new stdClass();
                foreach ($aliases as $idType => $alias) {
                    $pluginQueryDataResponse->$artifactId->$idType = $alias;
                }
            }
        }
        return($pluginQueryDataResponse);
    }

    public function consolidateMetrics($pluginQueryDataInitial, $doc) {
        $pluginQueryDataResponse = $pluginQueryDataInitial;
        foreach ($doc->artifacts as $providerName => $content) {
            foreach ($content->artifacts as $artifactId => $aliases) {
                foreach ($aliases as $idType => $alias) {
                    $pluginQueryDataResponse->$artifactId->$idType = $alias;
                }
            }
        }
        return($pluginQueryDataResponse);
    }

    /**
     * Queries plugins
     * $type is "sources" or "aliases"
     * */
    public function queryPlugins($pluginUrls, $pluginQueryData, $pluginType) {
        #FB::log($pluginUrls);
        $response = new stdClass();
        $response->$pluginType = new stdClass();

        $pool = new HttpRequestPool();

        foreach ($pluginUrls as $sourceName => $pluginUrl) {
            $request = new HttpRequest($pluginUrl, HTTP_METH_GET);
            $dataToSend = new stdClass();
            $id = $pluginQueryData->id;
            $dataToSend->$id = $pluginQueryData->aliases;
            $encoded_data = json_encode($dataToSend);
            #$doc->status->encoded_data = $encoded_data;
            #$request->setPostFields(array('query' => $encoded_data)); #use with HTTP_METH_POST
            $request->setQueryData(array('query' => $encoded_data)); #use with HTTP_METH_GET
            $request->setOptions(array('timeout' => 250));
            #FB::log($request);
            $pool->attach($request);
        }

        try {
            $pool->send();
        } catch (HttpRequestPoolException $e) {
            breadcrumb($e, 0);
        }

        foreach ($pool as $request) {
            $body = $request->getResponseBody();

            if ($body != "") {
                $pluginResponse = json_decode($body);
                if (isset($pluginResponse)) {
                    $sourceName = $pluginResponse->source_name;
                    $response->$pluginType->$sourceName = $pluginResponse;
                    breadcrumb("Got response from " . $sourceName);
                    #breadcrumb($body);
                }
            }
        }
        return $response;
    }

    /**
     * Updates the collection by calling alias plugins
     * */
//    public function getAliases($id) {
//        #get initial list
//        $pluginQueryData = new stdClass();
//        $pluginQueryData->id = $id;
//        $pluginQueryData->aliases = new stdClass();
//        $config = new Zend_Config_Ini(CONFIG_PATH, ENV);
//
//        # call alias plugins sequentially
//        $pluginUrls = $config->plugins->alias;
//        foreach ($pluginUrls as $sourceName => $pluginUrl) {
//            $doc = $this->queryPlugins(array("alias" => $pluginUrl), $pluginQueryData, "aliases");
//            $pluginQueryData = $this->consolidateAliases($pluginQueryData, $doc);
//        }
//        return($pluginQueryData);
//    }

    /**
     * Makes an array of artifacts to genre decisions
     */
    private function getBestGenre(stdClass $sources, $lookup_id) {
        $list_of_all = array();
        foreach ($sources as $source) {
            $sourceName = $source->source_name;
            // we want to figure the genre, but each Source has its own opinion on that.
            // so we gather them all.
            foreach ($source->artifacts as $id => $item) {
                if (isset($source->artifacts->$lookup_id->type)) {
                    $list_of_all[$sourceName] = $source->artifacts->$lookup_id->type;
                }
            }
        }
        #FB::log($list_of_all);
        $flipped = array_flip($list_of_all);
        #FB::log($flipped);
        # set to first as backup plan
        $genre = reset($list_of_all);
        # now iter and get the first that isn't "unknown" if there is one
        foreach ($list_of_all as $candidate) {
            if (($candidate != "unknown") and ($candidate != "generic")) {
                $genre = $candidate;
                break;
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
    private function sortByArtifact(stdClass $sources) {
        $artifacts = new StdClass;
        foreach ($sources as $source) {
            $sourceName = $source->source_name;

            // we want to figure the genre, but each Source has its own opinion on that.
            // so we gather them all.
            foreach ($source->artifacts as $id => $item) {
                if (!isset($artifacts->$id)) {
                    $artifacts->$id = new stdClass();
                }
                $artifacts->$id->$sourceName = $item;
            }
        }
        #FB::log($genres);

        return $artifacts;
    }

    private function getIcon($sourceName, $metricName, $abouts) {
        $icon = "";
        if (isset($abouts->$sourceName->icon)) {
            if ($abouts->$sourceName->icon) {
                if (isset($abouts->$sourceName->icon->$metricName)) {
                    $icon = $abouts->$sourceName->icon->$metricName;
                } else {
                    $icon = $abouts->$sourceName->icon;
                }
            }
        }
        return($icon);
    }

    private function getTooltipText($sourceName, $metricName, $abouts) {
        $prettyMetricName = $this->getPrettyMetricName($metricName);
        $tooltiptext = $sourceName . " " . $prettyMetricName . ": " . $abouts->$sourceName->metrics->$metricName;
        return($tooltiptext);
    }

    public function getPrettyMetricName($metricName) {
        $prettyMetricName = str_replace("_", " ", $metricName);
        $semicolonLocation = strpos($prettyMetricName, ";");
        if ($semicolonLocation !== false) {
            #remove stuff after the semicolon
            $prettyMetricName = substr($prettyMetricName, 0, $semicolonLocation);
        }
        return($prettyMetricName);
    }

    private function getSourceAbouts(stdClass $sources) {
        $abouts = new StdClass;
        foreach ($sources as $source) {
            if (isset($source->source_name)) {
                $sourceName = $source->source_name;
                $abouts->$sourceName = $source->about;
            }
        }
        return $abouts;
    }

    public function extractMetricsInfo($id, $sources, $d="_") {

        $abouts = $this->getSourceAbouts($sources);

        $artifacts = $this->sortByArtifact($sources);
        $artifact = $artifacts->$id;
        $metrics_array = array();

        foreach ($artifact as $sourceName => $sourceData) {
            foreach ($sourceData as $metricName => $metricValue) {
                if ($showZeros or ($metricValue != 0)) {
                    if (!in_array($metricName, array("authors", "url", "title", "year", "journal", "doi", "pmid", "upload_year", "type", "show_details_url"))) {
                        $item = new stdClass();
                        $item->id = "$sourceName$d$metricName";
                        $item->value = $metricValue;
                        $item->last_update = $sources->$sourceName->last_update;
                        $item->drilldown_url = $sources->$sourceName->artifacts->$id->show_details_url;
                        $item->meta = new stdClass();
                        $item->meta->provider = $sourceName;
                        $item->meta->provider_url = $sources->$sourceName->about->url;
                        $item->meta->tooltiptext = $this->getTooltipText($sourceName, $metricName, $abouts);
                        $item->meta->icon = $this->getIcon($sourceName, $metricName, $abouts);
                        $item->meta->flavour = "";
                        $item->meta->license = "";
                        $item->meta->terms_of_use = "";
                        $item->meta->access_restrictions = "";
                        $item->meta->display_name = $this->getPrettyMetricName($metricName);
                        $metrics_array[json_encode($item)] = $item->value;
                    }
                }
            }
        }

        arsort($metrics_array);
        $response_all = array();
        foreach ($metrics_array as $key => $value) {
            $response_all[] = json_decode($key);
        }
        #$response_all = array_keys($metrics_array), 'json_decode');
        #FB::log($response_all);
        return($response_all);
    }

    public function getMetricsAndBiblio($id, $itemsWithAliases) {
        $config = new Zend_Config_Ini(CONFIG_PATH, ENV);

        # call alias plugins sequentially
        $pluginUrls = $config->plugins->source;
        $response = $this->queryPlugins($pluginUrls, $itemsWithAliases, "sources");

        return($response);
    }

    /**
     * Updates the collection by calling metrics plugins
     * */
//    public function getMetrics($id, $itemsWithAliases) {
//        $doc = $this->getMetricsAndBiblio($id, $itemsWithAliases);
//
//        $response = new stdClass();
//        $response->$id = $this->extractMetricsInfo($id, $doc->sources);
//        return($response);
//    }

    public function getBiblio($id, $itemsWithAliases, $showZeros=false) {
        $doc = $this->getMetricsAndBiblio($id, $itemsWithAliases);

        $response = new stdClass();
        $response->$id = $this->extractBiblioInfo($id, $doc->sources, $showZeros);
        return($response);
    }

    public function extractBiblioDetails($id, $sourceName, $sourceData, $abouts, $biblioSource, $showZeros) {
        $response = new stdClass();

        if ($sourceName == "CrossRef" and $biblioSource == "CrossRef") {
            $response->title = $sourceData->title;
            $response->authors = $sourceData->authors;
            $response->year = $sourceData->year;
            $response->repository = $sourceData->journal;
            $response->doi = "http://dx.doi.org/$sourceData->doi";
            $response->url = "http://dx.doi.org/$sourceData->doi";
        } elseif ($sourceName == "PubMed" and $biblioSource == "PubMed") {
            $response->title = $sourceData->title;
            $response->authors = $sourceData->authors;
            $response->year = $sourceData->year;
            $response->repository = $sourceData->journal;
            $response->url = "http://www.ncbi.nlm.nih.gov/pubmed/$sourceData->pmid";
            $response->pmid = $sourceData->pmid;
        } elseif ($sourceName == "Mendeley" and $biblioSource == "Mendeley") {
            $response->title = $sourceData->title;
            $response->authors = $sourceData->authors;
            $response->year = $sourceData->year;
            $response->repository = $sourceData->journal;
            $response->url = $sourceData->show_details_url;
        }
        if ($sourceName == "Slideshare") {
            $response->title = $sourceData->title;
            $response->repository = "Slideshare";
            $response->upload_year = $sourceData->upload_year;
        } elseif ($sourceName == "GitHub" or $sourceName == "SourceForge") {
            $response->title = $sourceData->title;
            $response->repository = $sourceName;
            $response->upload_year = $sourceData->upload_year;
        } elseif ($sourceName == "FigShare") {
            $response->title = $sourceData->title;
            $response->repository = "FigShare";
        } elseif ($sourceName == "Dryad") {
            $response->title = $sourceData->title;
            $response->authors = $sourceData->authors;
            $response->repository = "Dryad Data Repository";
            $response->url = "http://dx.doi.org/$sourceData->doi";
            $response->doi = "http://dx.doi.org/$sourceData->doi";
        }

        return ($response);
    }

    public function extractBiblioInfo($id, $sources, $showZeros=false) {
        $abouts = $this->getSourceAbouts($sources);

        $artifacts = $this->sortByArtifact($sources);
        $artifact = $artifacts->$id;

        $biblioSources = array("CrossRef", "PubMed", "Mendeley");
        $biblioSource = "";

        foreach ($biblioSources as $candidateSource) {
            if (isset($artifact->$candidateSource)) {
                if (isset($artifact->$candidateSource->title)) {
                    if (strlen($artifact->$candidateSource->title) > 0) {
                        $biblioSource = $candidateSource;
                        break;
                    }
                }
            }
        }

        $biblioSection = array();
        foreach ($artifact as $sourceName => $sourceData) {
            $biblio = $this->extractBiblioDetails($id, $sourceName, $sourceData, $abouts, $biblioSource, $showZeros);
            $biblio->genre = $this->getBestGenre($sources, $id);
            if (isset($biblio->title)) {
                $biblioSection[] = $biblio;
            }
        }

        return $biblioSection;
    }

}

#$a = new Models_Item;
#$item = json_decode('{    "id": "17375194",     "aliases": {       "doi": "10.1371\/journal.pone.0000308",       "pmcid": "PMC1817752",       "pmid": "17375194",       "url": "http:\/\/dx.plos.org\/10.1371\/journal.pone.0000308",       "attacheddatadoi": "doi:10.5061\/dryad.j2c4g"     }   }');
#$b = $a->getBiblio("17375194", $item);
#print_r($b);
