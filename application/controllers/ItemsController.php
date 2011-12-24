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

    public function postAction() {
        echo "post time!";
        $this->_forward('index');
    }

    public function putAction() {

    }

    public function deleteAction() {

    }

    /**
     * Gets a list of identifiers associated with a particular query to a particular provider.
     *
     * URL: /providers/:provider/links?id=(query)&type=[type]
     * example: /providers/Dryad/links?id=Otto%2C%20Sarah%20P.
     */
    public function linksAction() {

        $id = urldecode($this->_request->getParam("id"));
        $pluginName = $this->_request->getParam("pluginName");
        $type = $this->_request->getParam("type");
        $client = new Zend_Http_Client();

        $pluginClassName = "Models_Provider_" . $pluginName . ucfirst($type);
        $plugin = new $pluginClassName();
        $creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');

        if ($id) {
            $data = $plugin->fetchLinks($id, $client, $creds);
        }
        else {
            $this->getResponse()->setHttpresponseCode(404)
                    ->appendBody("This action requires a value for the 'id' argument.\n");
            $this->_helper->ViewRenderer->setNoRender(true);
            $this->_helper->layout()->disableLayout();
            return false;
        }
        $this->view->data = $data;
        $this->_forward('index');
    }




}
