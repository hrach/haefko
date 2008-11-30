<?php

# load application
require './../haefko/application.php';


$application = new Application();


# activate utoload for "/app/extends"
# $application->autoload();


# routing
# Router::connect();


$application->run();