<?php

$startTime = microtime(true);

require_once '../Haefko/Debug.php';
require_once '../Haefko/Application/Router.php';

echo Http::getRequestUrl() . '<br>';

Router::connect('/:controller{download}/:file');
Debug::dump(Router::$routing);

//Router::connect('/:lang{cs|en}/:action{show|delete|edit}/:id', array('controller' => 'codes'));
//Router::connect('/:lang{cs|en}/:action{add|}', array('action' => 'list', 'controller' => 'codes'));

echo '<br />nsp: ' . Router::$namespace . '<br>';
echo 'con: ' . Router::$controller . '<br>';
echo 'act: ' . Router::$action . '<br>';
echo 'arg: ' . print_r(Router::$args, true) . '<br>';


echo "\n<br/>time: " . round((microtime(true) - $startTime) * 1000, 2) . ' ms';