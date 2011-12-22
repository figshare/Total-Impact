<?php
class Models_Dryad extends Models_Provider {

    private $profilePageUri = "http://datadryad.org/discover?field=dc.contributor.author_filter&fq=dc.contributor.author_filter%3A[ID]";

    /**
     * Gets Dryad items IDs belonging to a certain author name
     *
     * @param string $profileId
     * @param Zend_Http_Client $http
     * @return array of Dryad item IDs
     */
    public function fetchLinks($profileId, Zend_Http_Client $http, Zend_Config_Ini $creds) {
        $profileId = urlencode(strtolower($profileId));
        $profileId = str_replace('+', '%5C+', $profileId);
        $url = str_replace("[ID]", $profileId, $this->profilePageUri);

        $http->setUri($url);
        $response = $http->request();

        $regex_pattern = '/(10.5061.dryad.*)<.span/U';
        preg_match_all($regex_pattern, $response->getBody(), $matches);
        $artifactIds = $matches[1];
        return $artifactIds;
    }

}
?>
