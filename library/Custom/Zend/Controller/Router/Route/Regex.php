<?php
/**
 * Fixes automatic urldecode-ing in the stock Zend version.
 *
 * @author jason
 */
class Custom_Zend_Controller_Router_Route_Regex extends Zend_Controller_Router_Route_Regex {

    public function match($path, $partial=false){
        $path = urlencode($path);
        return parent::match($path, $partial);
    }
}
?>
