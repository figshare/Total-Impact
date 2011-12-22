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
     * URL: /providers/:provider/links?q=(query)
     * example: /providers/Dryad/links?q=Otto%2C%20Sarah%20P.
     */
    public function linksAction() {
    
        $q = urldecode($this->_request->getParam("q"));
        $pluginName = $this->_request->getParam("pluginName");
        $client = new Zend_Http_Client();
        $dryad = new Models_Dryad();

        $pluginClassName = "Models_" . $pluginName;
        $plugin = new $pluginClassName();
        $creds = new Zend_Config_Ini(APPLICATION_PATH . '/config/creds.ini');

        if ($q) {
            $data = $plugin->fetchLinks($q, $client, $creds);
        }
        else {
            $this->getResponse()->setHttpresponseCode(404)
                    ->appendBody("This action requires a value for the 'q' argument.\n");
            $this->_helper->ViewRenderer->setNoRender(true);
            $this->_helper->layout()->disableLayout();
            return false;
        }
        $this->view->data = $data;
        $this->_forward('index');
    }




}
