<?php
/**
 * Description of collectionInput
 *
 * @author jason
 */
class CollectionInput {
   private $collectionTitle;
   private $idsStr;
   private $artifactIds;
   private $couch;

   function __construct(Couch_Client $couch) {
       $this->couch = $couch;
   }
   
   public function getArtifactIds() {
       return $this->artifactIds;
   }
   public function getCollectionId() {
       return $this->collectionId;
   }
   public function getCollectionTitle() {
       return $this->collectionTitle;
   }
   public function setCollectionTitle($collectionTitle) {
       $this->collectionTitle = $collectionTitle;
   }

   public function setIdsStr($idsStr) {
       $this->idsStr = $idsStr;
   }


   /**
    * Splits a string into lines
    *
    * @param String $str
    * @return Array the lines from the string
    */
   private function idsFromStr($str){
        $lines = preg_split("/[\s,]+/", $str);
        return $lines;
   }

    /**
     * Returns a string of random, mixed-case letters
     *
     * @param Int $length Length you want the returned ID to be
     * @return string
     */
    function randStr($length) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $ret = "";

	$size = strlen($chars);
	for($i = 0; $i < $length; $i++) {
            $index = mt_rand(0, $size - 1);
		$ret .= $chars[$index];
	}
	return $ret;
    }

    /**
     * Saves a collection to the database based on user input
     *
     * @param string $title Collection title
     * @param string $idsStr A list of artifact IDs, delimited by linebreaks\
     * @param string $ts Unix timestamp as a string, useful for testing
     * @return StdClass A CouchDB response object
     */
    public function save($title, $idsStr) {
        // sanitize inputs
        $title = strip_tags($title);
        $idsStr = strip_tags($idsStr);

        // build the object
        $doc = new stdClass();
        $doc->_id = $this->randStr(6);
        $doc->created_at = (string)time();
        $doc->title = $title;
        $doc->artifact_ids = $this->idsFromStr($idsStr);
        $doc->sources = new stdClass(); // we'll fill this later
        $doc->updates = new stdClass(); // also for later

        // put it in couchdb
        $response = $this->couch->storeDoc($doc);
        return $response;
    }

}
?>
