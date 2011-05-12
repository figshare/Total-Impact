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
        echo "<h2><img src='./img/ajax-loader.gif'>Getting information now</h2>";
        $result = $this->couch->descending(true)
            ->include_docs(true)
            ->getView("main", "unupdated");
        sleep(1);
        
        if (count($result->rows) == 0){
            echo "Everything's up to date.";
        }
        else { // there's stuff that hasn't been updated.
        
            foreach ($result->rows as $row){
                sleep(1);
                $doc = $row->doc;
                echo "<h3>updating collection " . $doc->_id . "</h3>"; 
                foreach ($this->config['plugins'] as $sourceName=>$sourceUri){
                    echo "with $sourceName...";
                    $this->http->setUri($sourceUri);

                    $this->http->setRawData(json_encode($doc->artifact_ids), 'text/json');  
                    $result = $this->http->request("POST");
                    $doc->sources->{$sourceName} = json_encode($result->getBody());
                    $doc->updated = true;

                }
                sleep(1);
                echo $doc->_rev . "<br>";
                $this->couch->storeDoc($doc);
                echo "Uploading collection to the database...<br>";
                
            }
        }
    }
    
}

?>
