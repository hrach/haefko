<?php

require_once 'haefko/loader.php';
debug::init(true);

$cache = new Cache();
$cache->clean(array(
	'priority' => 5,
	'tags' => 'tag-test',
));