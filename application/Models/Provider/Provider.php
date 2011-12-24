<?php

abstract class Models_Provider_Provider {


    // Retrieves an array of items associated with a certain ID
    abstract public function fetchLinks($identifier, Zend_Http_Client $http, Zend_Config_Ini $creds);

    protected function makeFetchLinksResponse($ids){
        $ret = array();
        foreach ($ids as $id){
            $ret[] = array("namespace" => $this->namespace, "id" => $id);
        }
        return $ret;
    }
}
?>
