<?php

require 'haefko/loader.php';

$autoload = new Autoloader('./temp/');
$autoload->addDir(dirname(__FILE__) . '/../../haefko/libs');
$autoload->register();

echo "<pre>";
print_r($autoload->getClasses());
echo "</pre>";