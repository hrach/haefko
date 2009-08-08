<?php

require_once 'haefko/loader.php';
debug::init(true);



$cache = new Cache();
if ($cache->isCached('var')) {
	echo "cached: " . $cache['var'];
} else {
	$cache->set('var', 'variable', array(
		'expires' => 60,
		'sliding' => true,
	));

	echo "saved";
}