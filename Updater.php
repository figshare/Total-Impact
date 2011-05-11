<?php

/**
 * Description of Updater
 *
 * @author jay
 */
class Updater {
    private $couch;
    private $http;
    private $config;
    
    public function __construct(Couch_Client $couch, Zend_Http_Client $http, $config){
        $this->couch = $couch;
        $this->http = $http;
        $this->config = $config;
    }
    
    public function update(){
        $result = $this->couch->descending(true)
            ->include_docs(true)
            ->getView("main", "unupdated");
        
        $docs = $result->rows;
        foreach ($docs as $doc){
            foreach ($this->config['plugins'] as $sourceName=>$sourceUri){
                $this->http->setUri($sourceUri);
                echo file_get_contents($sourceUri);
//                $result = $this->http->request("GET");
//                echo $result->getMessage();
                echo "<br>";
                
            }
        }
    }
    
}

?>
