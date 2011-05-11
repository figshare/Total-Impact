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

    /**
     * The constructor.
     *
     * @param string $mode The mode, either debug or production
     */
    public function  __construct($id = 'null')
    {
        $this->id = $id;
    }
    
    public getMetrics() {
        return this->getTestMetrics();
    }
    
    public getTestMetrics() {
        this->method = 'GET';
        this->metric_name = 'Readership';
        this->metric_value = 50;
        this->source_name = 'Mendeley';
        this->icon = 'http://www.mendeley.com/favicon.ico';
        this->type = 'Article';
    }
}
