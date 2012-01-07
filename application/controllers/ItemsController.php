<?php

class ItemsController extends Zend_Controller_Action {

    private $item;
    private $aliasProviders;

    public function init() {

        if (!$this->_request->getActionName()) {
            $this->_request->setActionName('index');
        }

        // display stuff
        $this->_helper->contextSwitch()
                ->setContext(
                        'html', array(
                            'suffix' => 'html',
                            'headers' => array('Content-Type' => 'text/html; Charset=UTF-8')
                        )
                )
                ->addActionContext('index', array('html', 'xml', 'json'))
                ->setAutoJsonSerialization(false)
                ->initContext();

        // params stuff
        $namespace = $this->_request->getParam("namespace");
        $id = $this->_request->getParam("id");


        // wiring up the object graph
        $creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/config/application.ini', 'production');
        $couch = new Couch_Client($creds->db->dsn, "mytestdb");
        $aliases = new Models_Aliases(array($namespace => $id));
        $this->item = new Models_Item($aliases, $couch);

        $providersArr = explode(',', $config->aliasProviders);
        foreach ($providersArr as $modelName){
            $fullModelName = "Models_Provider_" . ucfirst($modelName);
            $this->aliasProviders[] = new $fullModelName(new Zend_Http_Client(), $config);
        }


    }

    public function preDispatch() {

    }

    public function indexAction() {
        print_r($this->_request);
        $this->view->data = $this->item->retrieve();
    }

    /*
     * For testing with items from the testdb:
     * /items/1 #item with total-impact ID= 1
     * /items/0000000002?namespace=PubMed #item with PMID=0000000002
     */
    public function getAction() {
        echo "i am the get action";
        $this->_forward('index');

    }

    /*
     * handles POST requests by forwarding them to the appropriate action
     */
    public function postAction() {
        $namespace = $this->_request->getParam("namespace");
        $id = $this->_request->getParam("id");
        print_r($this->_request);
        $this->_forward('index');
    }

    // curl -i -H "Accept: application/json" -X POST -d "name=10.1038/nature04863&namespace=DOI" http://total-impact.org.vm/items/create
    public function createAction() {
        $this->item->create();
        $this->item->update($this->aliasProviders);
        $this->view->data = true;
        $this->_forward('index');
    }

    // curl -i -H "Accept: application/json" -X POST -d "name=10.1038/nature04863&namespace=DOI" http://total-impact.org.vm/items/update
    public function updateAction() {

        try {
            $this->item->update($this->aliasProviders);
            $this->view->data = true;
        } catch (Exception $e) {
            $this->getResponse()->setHeader('Status', '404 Item not found');
        }
        $this->_forward('index');
    }

}
