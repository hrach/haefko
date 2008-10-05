<?php

require_once 'Haefko/Application.php';
$app = new Application();

/* pouze pokud vyuzivate /app/extends */
// $app->autoload();

/* routing */

$app->run();