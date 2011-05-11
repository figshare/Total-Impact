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
     * @url GET total-impact/mendeley/metrics/:id
     * @url GET total-impact/mendeley/metrics
     */
    public function getMetrics($id = null)
    {
    
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
