<?php
class SlidesharePluginController
{
    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET total-impact/plugins/slideshare
     */
    public function index() 
    {
        return "This is the Slideshare Plugin";
    }


    /**
     * Returns metrics values for a Slideshare	 String ID
     *
     * @url POST total-impact/plugins/slideshare/metrics
     */
    public function getMetrics($data)
    {  
        // read data 
        $mArray = array();
        foreach ($data as $id) {        
            $mList = new MetricList();
            $mList->setId($id);
            $mList->setMethod('Post');
            $mList->setSourceName('Slideshare');
            $mList->setIcon('http://www.slideshare.net/favicon.ico');
            $mList->setType('Slides');            
            $mList->setPlugin('slideshare.py');
            $mList->getMetrics(); // possible metrics loading method
            $mArray[$id]=$mList;
        }
        
        return $mArray; // serializes object into JSON
    }
}
?>
