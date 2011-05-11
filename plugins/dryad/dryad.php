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
        // run python code and get real metrics
        // system('python mendeley.py ')
        // parse to json
        $this->getTestMetrics();
    }
    
    public function getTestMetrics() {
        $this->id = '10.22212/332';
        $this->method = 'GET';
        $this->metric_name = 'Readership';
        $this->metric_value = 50;
        $this->source_name = 'Mendeley';
        $this->icon = 'http://www.mendeley.com/favicon.ico';
        $this->type = 'Article';
    }
}
?>
