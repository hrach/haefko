<?php

require_once 'hf/HF/Application/Application.php';

$app = Application::getInstance();
$app->loadCore('Autoload')
    ->loadConfig();

$autoload = Autoload::getInstance();
$autoload->addFramework()
         ->addDir($app->getPath())
         ->register();


Router::addService('rss');
Router::connect('/:action{groups|create|}', array('controller' => 'messages', 'action' => 'groups'));
Router::connect('/:action{messages}/:group', array('controller' => 'messages'));
Router::connect('/:action{edit}/:message', array('controller' => 'messages'));

$app->run();