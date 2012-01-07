<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initLoaderResource()
    {
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
        return $loader;
    }

    protected function _initSpecialRoutes()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $route = new Zend_Controller_Router_Route_Regex(
                'items/([^/]+)/([^/]+?)(?:/(.*?))?(?:\.(json|html|xml))?',
                array(
                    'controller' => 'items',
                    'action' => 'index',
                    'format' => 'json'
                    ),
                array(
                    1 => "namespace",
                    2 => "id",
                    3 => "action",
                    4 => "format"
                    )
                );
        $router->addRoute("items", $route);
    }

}

