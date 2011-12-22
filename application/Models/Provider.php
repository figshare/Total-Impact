<?php

abstract class Models_Provider {
    /**
     * Retrieves an array of items associated with a certain ID
     *
     * @param string $identifier
     * @param Zend_Http_Client $http
     * @return array of items stored by the provider
     */
    abstract public function fetchLinks($identifier, Zend_Http_Client $http, Zend_Config_Ini $creds);
}
?>
