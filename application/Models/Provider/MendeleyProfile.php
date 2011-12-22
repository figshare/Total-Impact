<?php

class Models_Provider_MendeleyProfile extends Models_Provider_Mendeley {

    private $client;
    private $creds;

    /**
     * Gets items IDs in a certain Mendeley profile
     *
     * @param string $profileId whose profile it is
     * @param Zend_Http_Client $http
     * @param Zend_Config_Ini $creds
     * @return array the items in the profile
     */
    public function fetchLinks($profileId, Zend_Http_Client $http, Zend_Config_Ini $creds) {
        $this->http = $http;
        $this->creds = $creds;

        $body = $this->getAllMendeleyPubPages($profileId);
        $biblio_array = $this->extractMendeleyBiblio($body);
        #print_r($biblio_array);
        $ids = array();
        foreach ($biblio_array as $biblio) {
            $id = "";
            #print_r($biblio);
            if (array_key_exists("rft_id", $biblio)) {
                $regex_pattern = '/info.*?%2F(?P<id>.*)/';
                preg_match($regex_pattern, $biblio["rft_id"], $matches);
                if ($matches) {
                    $id = urldecode($matches["id"]);
                    $ids[] = $id;
                }
            }
            if ($id === "") {
                $lookupResponse = $this->lookUpMendeleyPaper($biblio);
                if (isset($lookupResponse->documents)) {
                    $id = $lookupResponse->documents[0]->uuid;
                    $ids[] = $id;
                    #print_r("\nSUCCESS!\n");
                } else {
                    #print_r("\n");
                    #print_r($biblio);
                    #print_r(json_encode($lookupResponse) . "\n");
                }
            }
        }
        return($ids);
    }

    public function getMendeleyProfilePage($profileId, $suffix="") {

        $mendeleyUrlProfilePage = "http://www.mendeley.com/profiles/" . $profileId . $suffix . "/";

        $requestProfilePage = new HttpRequest($mendeleyUrlProfilePage, HTTP_METH_GET);
        $this->http->setUri($mendeleyUrlProfilePage);
        $response = $this->http->request();
        $bodyProfilePage = $response->getBody();

        return $bodyProfilePage;
    }

    public function getAllMendeleyPubPages($profileId) {
        $bodyPublicationsPage = $this->getMendeleyProfilePage($profileId, "/publications");

        $regex_pattern = '|http://www.mendeley.com/profiles/' . $profileId . '/publications/(\w+)/|Ums';
        preg_match_all($regex_pattern, $bodyPublicationsPage, $subsections, PREG_SET_ORDER);

        $body = $bodyPublicationsPage; // starts off with a default section
        foreach ($subsections as $subsection) {
            $bodySubsectionPublicationsPage = $this->getMendeleyProfilePage($profileId, "/publications/" . $subsection[1]);
            $body .= $bodySubsectionPublicationsPage;
        }
        return($body);
    }

    public function extractMendeleyBiblio($body) {
#<span class="Z3988" title="ctx_ver=Z39.88-2004&amp;rfr_id=info%3Asid%2Fmendeley.com%2Fmendeley&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal&amp;rft.genre=proceeding&amp;rft.date=2008&amp;rft.pages=4&amp;rft.atitle=Linking+database+submissions+to+primary+citations+with+PubMed+Central&amp;rft.aulast=Piwowar&amp;rft.aufirst=Heather+A&amp;rft.au=Chapman%2C+Wendy+W"></span>
        $all_artifacts = array();
        $regex_pattern = '/<span class="Z3988" title="(.*)"><.span>/Ums';
        preg_match_all($regex_pattern, $body, $hits, PREG_SET_ORDER);
        foreach ($hits as $hit) {
            $hit_sections = explode("&amp;", $hit[1]);
            $biblio = array();
            foreach ($hit_sections as $section) {
                $section_parts = explode("=", $section);
                $biblio[$section_parts[0]] = $section_parts[1];
            }
            $all_artifacts[] = $biblio;
        }
        return($all_artifacts);
    }

    public function lookUpMendeleyPaper($biblio) {
        $MENDELEY_LOOKUP_FROM_DOI_URL_PART1 = "http://api.mendeley.com/oapi/documents/search/title%3A";
        $MENDELEY_LOOKUP_FROM_DOI_URL_PART2 = "/?consumer_key=" . $this->creds->apis->mendeley_key;

        $title = $biblio["rft.atitle"];
        $title = preg_replace('/%\w\w/', '', $title);

        $mendeleyUrlLookupPage = $MENDELEY_LOOKUP_FROM_DOI_URL_PART1 . $title . "%20year%3A" . $biblio["rft.date"] . "%20authors%3A" . $biblio["rft.aulast"] . $MENDELEY_LOOKUP_FROM_DOI_URL_PART2;
        #print_r("\n" . $mendeleyUrlLookupPage);
        $requestLookupPage = new HttpRequest($mendeleyUrlLookupPage, HTTP_METH_GET);
        $requestLookupPage->setOptions(array("timeout" => 10, "useragent" => "total-Impact"));

        try {
            $responseLookupPage = $requestLookupPage->send();
            $bodyLookupPage = $responseLookupPage->getBody();
            $bodyLookupJson = json_decode($bodyLookupPage);
        } catch (Exception $ex) {
            $bodyLookupJson = null;
        }

        return($bodyLookupJson);
    }
}

?>
