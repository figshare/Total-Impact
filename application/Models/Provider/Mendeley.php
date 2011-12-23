<?php

class Models_Provider_Mendeley extends Models_Provider_Provider {

    protected $namespace = "Mendeley";

    // Retrieves an array of items associated with a certain ID
    public function fetchLinks($identifier, Zend_Http_Client $http, Zend_Config_Ini $creds) {
        throw new Exception("fetchLinks needs to be overridden");
    }
}
?>
