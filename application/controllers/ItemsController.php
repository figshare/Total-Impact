<?php

class ItemsController extends Zend_Rest_Controller {

    private $item;
    private $aliasProviders;

    public function init() {
        // hack b/c ContextSwitch::setDefaultContext doesn't work: http://framework.zend.com/issues/browse/ZF-8257
        if (!$this->_request->getParam('format')) {
            $this->_request->setParam('format', 'json');
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
        $id = $this->_request->getParam("name"); // annoyingly, "id" is a reserved param used by the Zend Request obj
        if (!$namespace) {
            $namespace = "totalimpact";
        }

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
        
    }

    /*
     * For testing with items from the testdb:
     * /items/1 #item with total-impact ID= 1
     * /items/0000000002?namespace=PubMed #item with PMID=0000000002
     */
    public function getAction() {
        $this->item->retrieve();
        $this->view->data = $this->item->retrieve();
        $this->_forward('index');

    }

    /*
     * handles POST requests by forwarding them to the appropriate action
     */
    public function postAction() {
        $methodName = $this->_request->getParam("id");
        if (method_exists($this, $methodName. "Action")) {
            $this->_forward($methodName);
        }
        else {
            // throw a useful exception
        }
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


    public function putAction() {
        // strangely, Zend_Rest_Router directs any POST requests with parameters
        // to the PUT action.
        // this is a a hacky fix...should be fixed by extending the REST router.
        $this->_forward('post');
    }

    public function deleteAction() {

    }
}
