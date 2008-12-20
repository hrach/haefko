<?php

require '../../haefko/libs/autoload.php';

$autoload = new Autoload();
$autoload->addDir(dirname(__FILE__) . '/../../haefko/libs');
$autoload->load();

print_r(file_get_contents($autoload->cache));