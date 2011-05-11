<?php
/**
 * Class SlideshareMetric.php
 * Wraps the mendeley plugin in python 
 * 
 */

class Metric {
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

    public function setId($id=null){
        $this->id=$id;
    }
    public function setMethod($method=null){
        $this->method=$method;
    }
    public function setMetricName($name=null){
        $this->metric_name=$name;
    }
    public function setMetricValue($value=null){
        $this->metric_value=$value;
    }
    public function setSourceName($sname=null){
        $this->source_name=$sname;
    }
    public function setIcon($icon=null){
        $this->icon=$icon;
    }
    public function setType($type=null){
        $this->type=$type;
    }
}
?>
