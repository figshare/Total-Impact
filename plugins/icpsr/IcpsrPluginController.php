<?php
class IcpsrPluginController
{
    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET /plugins/icpsr
     */
    public function index()
    {
        return "Hello World!!!!";
    }


    /**
     * Returns metrics values for a Icpsr String ID
     *
     * @url POST /plugins/icpsr/metrics
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
            $mList->setSourceName('Icpsr');

            // Here should be place the code to identify the type of artifact to process 
            // and filter those that can't be processed
            $mList->setIcon('http://www.icpsr.umich.edu/favicon.ico');
            $mList->setType('Article');            
            $mList->setPlugin('icpsr.py');
            $mList->getMetrics(); // possible metrics loading method
            $mArray[$id]=$mList;
        }
        
        return $mArray; // serializes object into JSON
    }
}
?>
