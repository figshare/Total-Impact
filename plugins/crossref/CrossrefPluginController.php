<?php
class CrossrefPluginController
{
    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET /plugins/crossref
     */
    public function index()
    {
        return "Hello World!!!!";
    }


    /**
     * Returns metrics values for a Crossref String ID
     *
     * @url POST /plugins/crossref/metrics
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
            $mList->setSourceName('Crossref');
            $mList->setIcon('http://www.crossref.org/favicon.ico');

            // Here should be place the code to identify the type of artifact to process 
            // and filter those that can't be processed
            $mList->setType('Article');            
            $mList->setPlugin('crossref.py');
            $mList->getMetrics(); // possible metrics loading method
            $mArray[$id]=$mList;
        }
        
        return $mArray; // serializes object into JSON
    }
}
?>
