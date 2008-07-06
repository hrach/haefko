<?php

require_once '../../Haefko/Application/Application.php';

$app = Application::create();

Application::loadCore('Autoload');
$autoload = Autoload::getInstance();
$autoload->addFramework()
         ->addDir($app->getPath())
         ->register();


Router::addService('rss');

Router::connect('/:controller{groups|}', array('controller' => 'groups'));
Router::connect('/:controller{messages}/:group{\d+}', array('action' => 'group'));
Router::connect('/:controller{messages}/:action{create|edit}/*');


$app->run();