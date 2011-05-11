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
        $mArray = array();
        foreach ($data as $id) {   
            $mList = new MetricList();
            $mList->setId($id);
            $mList->setMethod('Post');
            $mList->setSourceName('Dryad');
            $mList->setIcon('http://datadryad.org/themes/Dryad/images/dryadLogo.png');
            $mList->setType('Dataset');            
            $mList->setPlugin('dryad.py');
            $mList->getMetrics(); // possible metrics loading method
            $mArray[$id]=$mList;
        }
        
        return $mArray; // serializes object into JSON
    }
}
?>
