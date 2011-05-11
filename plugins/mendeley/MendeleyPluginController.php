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
        $mArray = array();
        foreach ($data as $id) {        
            $mendeley = new Mendeley();
            $mendeley->setId($id);
            $mendeley->setMethod('Post');
            $mendeley->getMetrics(); // possible metrics loading method
            $mArray[$id]=$mendeley;
        }
        
        return $mArray; // serializes object into JSON
    }
}
?>
