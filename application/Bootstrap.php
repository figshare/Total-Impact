<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initLoaderResource()
    {
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
        return $loader;
    }

}

