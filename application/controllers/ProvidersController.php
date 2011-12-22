<?php

class ProvidersController extends Zend_Controller_Action {

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

    }

    public function preDispatch() {
        // if the action is a plugin, redirect to the links action
        // should do this by remapping the path tin the router instead...
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/config/application.ini', 'production');
        $pluginNames = array();
        foreach ($config->plugins->source->toArray() as $pluginName => $url) {
            $pluginNames[] = ($pluginName);
        }
        $actionName = $this->_request->getActionName();
        if (in_array($actionName, $pluginNames)) {
            $this->_request->setParam("pluginName", $actionName);
            $this->_forward('links');;
        }
    }

    public function indexAction() {

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
