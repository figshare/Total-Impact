<?php

class ItemsController extends Zend_Rest_Controller {

    private $couch;
    private $creds;

    public function init() {
        // hack b/c ContextSwitch::setDefaultContext doesn't work: http://framework.zend.com/issues/browse/ZF-8257
        if (!$this->_request->getParam('format')) {
            $this->_request->setParam('format', 'json');
        }

        $this->_helper->contextSwitch()
                ->setContext(
                        'html', array(
                    'suffix' => 'html',
                    'headers' => array(
                        'Content-Type' => 'text/html; Charset=UTF-8',
                    ),
                        )
                )
                ->addActionContext('index', array('html', 'xml', 'json'))
                ->setAutoJsonSerialization(false)
                ->initContext();
        $this->creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');
        $this->couch = new Couch_Client($this->creds->db->dsn, "mytestdb");


    }

    public function preDispatch() {

    }

    public function indexAction() {
        echo "index";
    }

    public function getAction() {
        $namespace = $this->_request->getParam("namespace");
        $id = $this->_request->getParam("id");
        if (!$namespace) {
            $namespace = "totalimpact";
        }
        $aliases = new Models_Aliases(array($namespace => $id));
        $item = new Models_Item($aliases, $this->couch);
        $item->retrieve();
        $this->view->data = $item->getDoc();
        $this->_forward('index');

    }

    // curl -i -H "Accept: application/json" -X POST -d "name=10.1038/nature04863&namespace=DOI" http://total-impact.org.vm/items/create
    public function postAction() {
        var_dump($this->_request);
        $this->_forward('index');
    }


    public function putAction() {
        // strangely, Zend_Rest_Router directs any POST requests with parameters
        // to the PUT action.
        // this is a a hacky fix
        $this->_forward('post');
    }

    public function deleteAction() {

    }
}
