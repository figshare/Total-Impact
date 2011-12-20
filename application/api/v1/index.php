<?php

require_once '../../library/restler/restler.php';
require_once 'items.php';
require_once 'collections.php';
require_once 'providers.php';
require_once 'users.php';
require_once 'v1HtmlFormat.php';
require_once 'v1XmlFormat.php';

#spl_autoload_register('spl_autoload');

# give Restler an arg of TRUE in production to cache the url->method mappings
$r = new Restler();
$r->setSupportedFormats('JsonFormat', 'V1XmlFormat', 'V1HtmlFormat');
$r->addAPIClass('Collections');
$r->addAPIClass('Items');
$r->addAPIClass('Providers');
$r->addAPIClass('Users');
$r->handle();