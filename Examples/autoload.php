<?php

require '../Haefko/Autoload.php';

$autoload = Autoload::getInstance();
$autoload->addFramework();
$autoload->register();

$app = Application::getInstance();