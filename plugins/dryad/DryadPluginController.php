<?php
class DryadPluginController
{
    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET total-impact/dryad
     */
    public function index()
    {
        return "This is the Dryad Plugin";
    }


    /**
     * Returns metrics values for a Dyrad	 String ID
     *
     * @url POST total-impact/dryad/metrics
     */
    public function getMetrics($data)
    {  
        // read data 
        $dArray = array();
        foreach ($data as $id) {        
            $dryad = new Dryad();
            $dryad->setId($id);
            $dryad->setMethod('Post');
            $dryad->getMetrics(); // possible metrics loading method
            $dArray[$id]=$dryad;
        }
        
        return $dArray; // serializes object into JSON
    }
}
?>
