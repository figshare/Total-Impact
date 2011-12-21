<?php
class Models_Dryad extends Models_Provider {

    private $profilePage = "http://datadryad.org/discover?field=dc.contributor.author_filter&fq=dc.contributor.author_filter%3A%s";

    public function getArtifactIds($profileId, Zend_Http_Client $http) {
        $profileId = urlencode(strtolower($profileId));
        $profileId = str_replace('+', '%5C+', $profileId);
        $url = sprintf($this->profilePage, $profileId);
        
        $reques = new HttpRequest($url, HTTP_METH_GET);
        $response = $requestProfilePage->send();
        $body = $responseProfilePage->getBody();

        $regex_pattern = '/(10.5061.dryad.*)<.span/U';
        preg_match_all($regex_pattern, $body, $matches);
        $artifactIds = $matches[1];
        return $artifactIds;
    }
}
?>
