<?php

require '../Haefko/autoload.php';

$autoload = Autoload::getInstance();
$autoload->addFramework();
$autoload->register();

$app = Application::getInstance();