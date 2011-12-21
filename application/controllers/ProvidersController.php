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
                ->setAutoJsonSerialization(true)
                ->initContext();
    }

    public function indexAction() {

    }

    public function dryadAction() {
        $id = "Otto, Sarah P.";
        $dryad = new Models_Dryad();
        $data = $dryad->getDryadProfileArtifacts($id);
        
        $this->view->data = $data;
        $this->_forward('index');
    }



}
