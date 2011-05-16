<?php
class DryadPluginController
{
    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET total-impact/plugins/dryad
     */
    public function index()
    {
        return "This is the Dryad Plugin";
    }


    /**
     * Returns metrics values for a Dyrad	 String ID
     *
     * @url POST total-impact/plugins/dryad/metrics
     */
    public function getMetrics($data)
    {  
        $mArray = array();
        foreach ($data as $id) {   
            $mList = new MetricList();
            $mList->setId($id);
            $mList->setMethod('Post');
            $mList->setSourceName('Dryad');       $mList->setIcon('http://dryad.googlecode.com/svn-history/r4402/trunk/dryad/dspace/modules/xmlui/src/main/webapp/themes/Dryad/images/favicon.ico');
            $mList->setType('Dataset');            
            $mList->setPlugin('dryad.py');
            $mList->getMetrics(); // possible metrics loading method
            $mArray[$id]=$mList;
        }
        
        return $mArray; // serializes object into JSON
    }
}
?>
