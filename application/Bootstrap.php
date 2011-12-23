<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initLoaderResource()
    {
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
        return $loader;
    }
    protected function _initRestRoute()
    {
        $front     = Zend_Controller_Front::getInstance();
        $restRoute = new Zend_Rest_Route($front, array(), array('default' => array("items")));
        $front->getRouter()->addRoute('rest', $restRoute);
    }

}

