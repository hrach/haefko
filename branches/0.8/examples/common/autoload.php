<?php

require '../../haefko/libs/autoload.php';

$autoload = new Autoload('./');
$autoload->addDir(dirname(__FILE__) . '/../../haefko/libs');
$autoload->load();

echo "<pre>";
print_r($autoload->getClasses());
echo "</pre>";