<?php
/**
 * Class Dryad.php
 * Wraps the mendeley plugin in python 
 *
 */

class Dryad {
    public $id;
    public $method;
    public $metric_name;
    public $metric_value;
    public $source_name;
    public $icon;
    public $type;

    //protected $map = array();
    //protected $errorClasses = array();
    //protected $cached;

    public function __construct() {  
       // CONSTRUCTOR CONTENT HERE  
    }  

    public function getMetrics() {
        $exec = 'python dryad.py '.$this->id;
        $result = exec($exec, $output, $retval);
        $result = str_replace("'", "", $result, $count);
        list($metric_name, $metric_value) = split(":", $result, 5);
        $metric_value = str_replace("}", "", $metric_value, $count);
        $metric_name = str_replace("{", "", $metric_name, $count);
        
        $this->metric_name = $metric_name;
        $this->metric_value = $metric_value;
        $this->source_name = 'Dryad';
        $this->icon = 'http://datadryad.org/themes/Dryad/images/dryadLogo.png';
        $this->type = 'Article';
        //$this->getTestMetrics();
    }
    
    public function getTestMetrics() {
        $this->id = '10.22212/332';
        $this->method = 'Post';
        $this->metric_name = 'Dataset Downloads';
        $this->metric_value = rand(30,100);
        $this->source_name = 'Dryad';
        $this->icon = 'http://datadryad.org/themes/Dryad/images/dryadLogo.png';
        $this->type = 'Article';
    }
     
    public function setId($id=null){
        $this->id=$id;
    }
    public function setMethod($method=null){
        $this->method=$method;
    }
}
?>
