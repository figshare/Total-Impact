<?php

class Models_Provider_GitHub extends Models_Provider_Provider {

    protected $namespace = "GitHub";

    /**
     * Gets GitHub repo URLs for a given user or org
     *
     * @param string $profileId
     * @param Zend_Http_Client $http
     * @param Zend_Config_Ini $creds
     * @return array URLs of github repos
     */
    public function fetchLinks($identifier) {
        return $this->getGithubArtifacts($this->apiUrl, $identifier, $this->http);
    }

    /**
     * Gets some artifacts from GitHub based on a supplied url and ID
     *
     * @param string $apiurl must have an "[ID]" token to be replaced by the $profileId
     * @param string $profileId
     * @param Zend_Http_Client $http
     * @return array 
     */
    private function getGithubArtifacts($apiurl, $profileId, Zend_Http_Client $http) {
        $url = str_replace('[ID]', $profileId, $apiurl);
        $http = $http->setUri($url);
        $response = $http->request();
        $body = json_decode($response->getBody());

        $id_list = array();
        foreach ($body as $repo) {
            if (isset($repo->name)) {
                $id_list[] = $profileId . "/" . $repo->name;
            }
        }
        return $this->makeFetchLinksResponse($id_list);
    }

}

?>
