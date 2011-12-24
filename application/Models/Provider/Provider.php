<?php

abstract class Models_Provider_Provider {

    protected $http;
    protected $creds;

    function __construct(Zend_Http_Client $http, Zend_Config_Ini $creds) {
        $this->http = $http;
        $this->creds = $creds;
    }


    // Retrieves an array of items associated with a certain ID
    abstract public function fetchLinks($identifier);

    protected function makeFetchLinksResponse($ids){
        $ret = array();
        foreach ($ids as $id){
            $ret[] = array("namespace" => $this->namespace, "id" => $id);
        }
        return $ret;
    }
}
?>
