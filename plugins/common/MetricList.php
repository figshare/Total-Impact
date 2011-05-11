<?php
/**
 * Class MetricList.php
 * Wraps the mendeley plugin in python 
 *
 */

class MetricList {
    public $id;
    public $method;
    public $source_name;
    public $icon;
    public $type;
     
    public $plugin;
    
    public $list = array();
    
    public function __construct() {  
       // CONSTRUCTOR CONTENT HERE  
    }  

    public function getMetrics() {
        
        $exec = 'python '. $this->plugin;
        $exec .= ' ';
        $exec .= $this->id;
        $result = exec($exec, $output, $retval);
        
        $result = str_replace("'", "", $result, $count);
        $metrics = split(",", $result);
        
        $count = 0;
        foreach ($metrics as $metric) {        
            $m = new Metric();
            $m->setId($this->id);
            $m->setMethod($this->method);
            $m->setSourceName($this->source_name);
            $m->setIcon($this->icon);
            $m->setType($this->type);
            
            list($metric_name, $metric_value) = split(":", $metric);
            $metric_value = str_replace("}", "", $metric_value, $count);
            $metric_name = str_replace("{", "", $metric_name, $count);
            
            $m->setMetricName($metric_name);
            $m->setMetricValue($metric_value);

            $this->list[$count]=$m;
            $count++;
        }
    }
    
    public function setId($id=null){
        $this->id=$id;
    }
    public function setMethod($method=null){
        $this->method=$method;
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
    public function setPlugin($type=null){
        $this->plugin=$type;
    }
}
?>
