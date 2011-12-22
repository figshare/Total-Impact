<?php
class Models_Provider_Dryad extends Models_Provider_Provider {

    private $profilePageUri = "http://datadryad.org/discover?field=dc.contributor.author_filter&fq=dc.contributor.author_filter%3A[ID]";


    /**
     * Gets Dryad items IDs belonging to a certain author name
     *
     * @param string $name the name of the dataset creator
     * @param Zend_Http_Client $http
     * @param Zend_Config_Ini $creds
     * @return array Dryad ids created by $profileId
     */
    public function fetchLinks($name, Zend_Http_Client $http, Zend_Config_Ini $creds) {
        $name = urlencode(strtolower($name));
        $name = str_replace('+', '%5C+', $name);
        $url = str_replace("[ID]", $name, $this->profilePageUri);

        $http->setUri($url);
        $response = $http->request();

        $regex_pattern = '/(10.5061.dryad.*)<.span/U';
        preg_match_all($regex_pattern, $response->getBody(), $matches);
        $artifactIds = $matches[1];
        return $artifactIds;
    }

}
?>
