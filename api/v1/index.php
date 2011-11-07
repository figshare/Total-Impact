<?php

require_once '../../library/restler/restler.php';

spl_autoload_register('spl_autoload');

$r = new Restler();
$r->setSupportedFormats('JsonFormat', 'V1XmlFormat', 'V1HtmlFormat');
$r->addAPIClass('Collections');
$r->addAPIClass('Items');
$r->addAPIClass('Providers');
$r->addAPIClass('Users');
$r->handle();