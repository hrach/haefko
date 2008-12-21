<?php

# load application
require './../haefko/application.php';


$application = new Application();


# activate utoload for "/app/extends"
# $application->autoload();


# routing
# $router = $application->getRouter();
# $router->connect(...);


$application->run();