<?php
class PlosalmPluginController
{
    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET /plugins/plos
     */
    public function index()
    {
        return "Hello World!!!!";
    }


    /**
     * Returns metrics values for a Plos String ID
     *
     * @url POST /plugins/plosalm/metrics
     * @url POST /metrics
     */
    public function getMetrics($data)
    {  
        // read data 
        $mArray = array();
        foreach ($data as $id) {   
            $mList = new MetricList();
            $mList->setId($id);
            $mList->setMethod('Post');
            $mList->setSourceName('Plosalm');
            $mList->setIcon('http://www.plos.org/favicon.ico');

            // Here should be place the code to identify the type of artifact to process 
            // and filter those that can't be processed
            $mList->setType('Article');            
            $mList->setPlugin('plosalm.py');
            $mList->getMetrics(); // possible metrics loading method
            $mArray[$id]=$mList;
        }
        
        return $mArray; // serializes object into JSON
    }
}
?>
