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

 
        return $dryad; // serializes object into JSON
    }
}
?>
