<?php

require_once 'haefko/loader.php';
debug::init(true);

$cache = new Cache();
$cache->clean();