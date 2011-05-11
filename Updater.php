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
        
        foreach ($result->rows as $row){
            $doc = $row->doc;
            foreach ($this->config['plugins'] as $sourceName=>$sourceUri){
                $this->http->setUri($sourceUri);
                print_r($doc->artifact_ids);
                
                print_r(json_encode($doc->artifact_ids));
                echo "<br><br>";
                $this->http->setRawData(json_encode($doc->artifact_ids), 'text/json');  
                $result = $this->http->request("POST");
                $doc->sources->{$sourceName} = $result->getBody();
                
            }
        }
    }
    
}

?>
