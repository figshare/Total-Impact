<?php
class Models_Provider_Dryad extends Models_Provider_Provider {

    private $profilePageUri = "http://datadryad.org/discover?field=dc.contributor.author_filter&fq=dc.contributor.author_filter%3A[ID]";
    private $doiLookupUrl = "http://datadryad.org/solr/search/select/?q=dc.relation.isreferencedby:[ID]&fl=dc.identifier";
    protected $namespace = "Dryad";


    /**
     * Gets Dryad items IDs belonging to a certain author name
     *
     * @param string $name the name of the dataset creator
     * @param Zend_Http_Client $http
     * @param Zend_Config_Ini $creds
     * @return array Dryad ids created by $profileId
     */
    public function fetchLinks($name) {
        $name = urlencode(strtolower($name));
        $name = str_replace('+', '%5C+', $name);
        $url = str_replace("[ID]", $name, $this->profilePageUri);

        $this->http->setUri($url);
        $response = $this->http->request();

        $regex_pattern = '/10.5061.dryad\.(.*)<.span/U';
        preg_match_all($regex_pattern, $response->getBody(), $matches);
        $artifactIds = $matches[1];
        return $this->makeFetchLinksResponse($artifactIds);
    }

    public function addAliases(Models_Aliases $aliasesObj) {
        $doi = $aliasesObj->getId("DOI");
        if ($doi) {
            $url = str_replace('[ID]', $doi, $this->doiLookupUrl);
            $this->http->setUri($url);
            $response = $this->http->request();
            $xml = simplexml_load_string($response->getBody());
            $dryadDoi = (string)$xml->result->doc->arr->str[0];

            preg_match('#dryad\.(.+)$#', $dryadDoi, $m);
            $aliasesObj->addAlias("Dryad", $m[1]);

        }
        return $aliasesObj;
    }

}
?>
