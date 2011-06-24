<?php
/**
 * A fake couch_client for testing
 *
 * @author jason
 */
class Tests_Fakes_Couch extends Couch_Client {
    private $viewsToReturn;
    private $showsToReturn;
    private $viewReturnIndex;
    private $showReturnIndex;
    private $storedDocs;
    private $docsToGet;

    public function  __construct() {
        $this->viewReturnIndex = 0;
        $this->showReturnIndex = 0;
        $this->storedDocs = array();
    }
    public function setViewReturns(Array $viewsToReturn) {
        $this->viewsToReturn = $viewsToReturn;
    }
    public function setShowsToReturn($showsToReturn) {
        $this->showsToReturn = $showsToReturn;
    }
    public function setDocsToGet(Array $docsToGet) {
        $this->docsToGet = $docsToGet;
    }

    public function getStoredDocs($index) {
        if (!isset($this->storedDocs[$index])){
            throw new Exception("No doc has been stored at index $index.");
        }
        return $this->storedDocs[$index];
    }




    public function  getDoc($id) {
        if (isset($this->docsToGet[$id])){
            return $this->docsToGet[$id];
        }
        else {
            return '{"error":"not_found","reason":"missing"}';
        }
    }

    public function getView($id, $name){
        $thisView = $this->viewsToReturn[$this->viewReturnIndex];
        $this->viewReturnIndex++;
        return $thisView;
    }

    public function  getShow($id, $name) {
        $thisShow = $this->showsToReturn[$this->showReturnIndex];
        $this->showReturnIndex++;
        return $thisShow;
    }

    public function storeDoc($doc){
        $this->storedDocs[] = $doc;
    }
}
?>
