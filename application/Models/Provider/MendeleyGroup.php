<?php

class Models_Provider_MendeleyGroup extends Models_Provider_Mendeley {

    /**
     * Gets items IDs in a certain Mendeley group
     *
     * @param string $groupName
     * @param Zend_Http_Client $http
     * @param Zend_Config_Ini $creds
     * @return array the items in the group
     */
    public function fetchLinks($groupName, Zend_Http_Client $http, Zend_Config_Ini $creds) {

        $MENDELEY_LOOKUP_FROM_DOI_URL_PART1 = "http://api.mendeley.com/oapi/documents/groups/";
        $MENDELEY_LOOKUP_FROM_DOI_URL_PART2 = "/docs/?details=true&items=100&consumer_key=" . $creds->apis->Mendeley_key;
        $mendeleyUrlGroupPage = $MENDELEY_LOOKUP_FROM_DOI_URL_PART1 . $groupName . $MENDELEY_LOOKUP_FROM_DOI_URL_PART2;

        $http->setUri($mendeleyUrlGroupPage);
        $response = $http->request();
        $body = json_decode($response->getBody());

        $id_list = array();
        foreach ($body->documents as $artifact) {
            if (isset($artifact->url) && isset($artifact->uuid)) {
                $id_list[] = $artifact->uuid;
            }
        }

        return $this->makeFetchLinksResponse($id_list);
    }

}

?>
