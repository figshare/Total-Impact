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
   private $collectionId;

   function __construct($title, $idsStr) {
       $this->collectionTitle = $title;
       $this->idsStr = $idsStr;
       $this->artifactIds = $this->idsFromStr($idsStr);
       $this->collectionId = $this->randStr(5);
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


}
?>
