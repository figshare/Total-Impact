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
        $namespace = urldecode($this->_request->getParam("namespace"));
        $id = urldecode($this->_request->getParam("id"));


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
        $this->view->data = $this->item->retrieve();
    }



    // curl -X POST http://total-impact.org.vm/items/DOI/10.1038%2Fnature04863/create.html
    public function createAction() {
        $this->requirePost();
        $this->item->create();
        $this->item->update($this->aliasProviders);
        $this->_forward('index');
    }

    // curl -X POST http://total-impact.org.vm/items/DOI/10.1038%2Fnature04863/update.html
    public function updateAction() {
        $this->requirePost();
        try {
            $this->item->update($this->aliasProviders);
        } catch (Exception $e) {
            $this->getResponse()->setHeader('Status', '404 Item not found');
        }
        $this->_forward('index');
    }

    private function requirePost() {
        if (!$this->_request->isPost()) {
            $this->getResponse()->setHeader('Status', '404 Item not found');
            $this->_forward('index');
        }
    }

}
