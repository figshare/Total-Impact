<?php
class MendeleyPluginController
{
    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET total-impact/mendeley
     */
    public function index()
    {
        return "Hello World!!!!";
    }


    /**
     * Returns metrics values for a Mendeley String ID
     *
     * @url POST total-impact/mendeley/metrics
     */
    public function getMetrics($data)
    {  
        // read data 
        
        
        
        
        $mendeley = new Mendeley();
        if ($id) {
           $mendeley->getMetrics(); // possible metrics loading method
        } else {
           $mendeley->getMetrics(); // possible metrics loading method
        }

        return $mendeley; // serializes object into JSON
    }
}
?>
