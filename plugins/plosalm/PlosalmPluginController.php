<?php
class PlosalmPluginController
{
    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET total-impact/plugins/plosalm
     */
    public function index()
    {
        return "Hello World!!!!";
    }


    /**
     * Returns metrics values for a PLoS DOI String ID
     *
     * @url POST total-impact/plugins/plosalm/metrics
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
            $mList->setIcon('http://www.plosbiology.org/images/favicon.ico');
            $mList->setType('Article');            
            $mList->setPlugin('plosalm.py');
            $mList->getMetrics(); // possible metrics loading method
            $mArray[$id]=$mList;
        }
        
        return $mArray; // serializes object into JSON
    }
}
?>
