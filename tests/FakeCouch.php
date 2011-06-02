<?php
/**
 * A fake couch_client for testing
 *
 * @author jason
 */
class FakeCouch extends Couch_Client {
    private $viewReturns;
    private $viewReturnIndex;
    private $storedDocs;

    public function  __construct() {
        $this->viewReturnIndex = 0;
        $this->storedDocs = array();
    }

    public function setViewReturns(Array $viewReturns) {
        $this->viewReturns = $viewReturns;
    }
    public function getStoredDocs($index) {
        if (!isset($this->storedDocs[$index])){
            throw new Exception("No doc has been stored at index $index.");
        }
        return $this->storedDocs[$index];
    }


    public function getView($id, $name){
        $thisView = $this->viewReturns[$this->viewReturnIndex];
        $this->viewReturnIndex++;
        return $thisView;
    }

    public function storeDoc($doc){
        $this->storedDocs[] = $doc;
    }
}
?>
