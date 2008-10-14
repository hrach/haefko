<?php

# load application
require './haefko/application.php';
$app = new Application();

# load autoload for files in "/app/extends"
# $app->autoload();


# routing


# run application
$app->run();