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
        
        if (count($result->rows) == 0){
            echo "Everything's up to date.";
        }
        else { // there's stuff that hasn't been updated.
        
            foreach ($result->rows as $row){
                $doc = $row->doc;
                echo "<h2>updating " . $doc->_id . "</h2>"; 
                foreach ($this->config['plugins'] as $sourceName=>$sourceUri){
                    echo "with $sourceName...";
                    $this->http->setUri($sourceUri);

                    $this->http->setRawData(json_encode($doc->artifact_ids), 'text/json');  
                    $result = $this->http->request("POST");
                    $doc->sources->{$sourceName} = $result->getBody();
                    $doc->updated = true;
                    echo "Uploading a doc to the database:<br>";
                    print_r($doc);
                    echo "<br><br>";
                    $this->couch->storeDoc($doc);
                    sleep(1);

                }
            }
        }
    }
    
}

?>
