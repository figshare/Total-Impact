<?php
/**
 * Class Mendeley.php
 * Wraps the mendeley plugin in python 
 *
 */

class Mendeley {
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
        // run python code and get real metrics
        // system('python mendeley.py ')
        // parse to json
        $exec = 'python mendeley.py '.$this->id;
        $result = exec($exec, $output, $retval);
        $result = str_replace("'", "", $result, $count);
        list($metric_name, $metric_value) = split(":", $result, 5);
        $metric_value = str_replace("}", "", $metric_value, $count);
        $metric_name = str_replace("{", "", $metric_name, $count);
        
        $this->metric_name = $metric_name;
        $this->metric_value = $metric_value;
        $this->source_name = 'Mendeley';
        $this->icon = 'http://www.mendeley.com/favicon.ico';
        $this->type = 'Article';
        //$this->getTestMetrics();
    }
    
    public function getTestMetrics() {
        $this->metric_name = 'Readership';
        $this->metric_value = rand(50,100);
        $this->source_name = 'Mendeley';
        $this->icon = 'http://www.mendeley.com/favicon.ico';
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