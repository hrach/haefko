<?php

require_once 'Haefko/Application/Application.php';

$app = Application::create();

Application::loadCore('Autoload');
$autoload = Autoload::getInstance();
$autoload->addFramework()
         ->addDir($app->getPath())
         ->register();


/*
routing
*/

$app->run();