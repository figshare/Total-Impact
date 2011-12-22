<?php

class Models_PubMed extends Models_Provider {

    private $grantEsearchUrl = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&retmax=100&tool=[NAME]&email=[EMAIL]&term=[ID]";

    /**
     * Retrieves an array of pubmed IDs associated with a given grant number
     *
     * @param string $grantId
     * @param Zend_Http_Client $http
     * @return array of articles associated with $grantId
     */
    public function fetchLinks($grantId, Zend_Http_Client $http, Zend_Config_Ini $creds) {
        $grantId = urlencode(strtolower($grantId));

        $urlBase = str_replace("[EMAIL]", $creds->id->email, $this->grantEsearchUrl);
        $urlBase = str_replace("[NAME]", $creds->id->name, $urlBase);

        $grantIdQ = "($grantId" . "[grant number] OR $grantId-*[grant number])";
        $grantIdQ = urlencode($grantIdQ);
        $url = str_replace("[ID]", $grantIdQ, $this->grantEsearchUrl);

        $http->setUri($url);
        $response = $http->request();

        $regex_pattern = '/<Id>(.*)<\/Id>/U';
        preg_match_all($regex_pattern,  $response->getBody(), $matches);
        $artifactIds = $matches[1];
        return $artifactIds;
    }

}

?>
