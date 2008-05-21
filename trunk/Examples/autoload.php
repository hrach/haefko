<?php

require 'hf/HF/autoload.php';

$autoload = Autoload::getInstance();
$autoload->addFramework();
$autoload->register();

$app = Application::getInstance();