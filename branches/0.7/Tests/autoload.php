<?php

require 'Haefko/Autoload.php';

$autoload = new Autoload();
$autoload->addDir(dirname(__FILE__));
$autoload->load();

print_r(file_get_contents($autoload->cache));