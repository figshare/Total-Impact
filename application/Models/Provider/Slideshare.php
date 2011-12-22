<?php

class Models_Provider_Slideshare extends Models_Provider_Provider {

    private $profilePageUrl = "http://www.slideshare.net/[ID]/presentations";

    /**
     * Gets slideshare presentation urls based on a username
     *
     * @param string $profileId
     * @param Zend_Http_Client $http
     * @param Zend_Config_Ini $creds
     * @return array of slideshare presentation urls
     */
    public function fetchLinks($profileId, Zend_Http_Client $http, Zend_Config_Ini $creds) {
        $url = str_replace("[ID]", $profileId, $this->profilePageUrl);
        $http->setUri($url);
        $response = $http->request();

        $regex_pattern = '/<a title=.* href="(.' . $profileId . '.*)"/U';
        preg_match_all($regex_pattern, $response->getBody(), $matches);
        $artifactIds = $matches[1];
        foreach ($artifactIds as &$value) {
            $value = "http://www.slideshare.net" . $value;
        }
        return $artifactIds;
    }

}

?>
