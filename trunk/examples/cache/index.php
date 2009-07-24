<?php

require_once 'haefko/loader.php';
debug::init(true);

$cache = new Cache();
//$cache->delete('var');


if (isset($cache['var'])) {
	echo "Cached: " . $cache['var'];
} else {
	$var = date('H:i.s');
	echo "saving. ". $var;
	$cache->save('var', $var, array(
		'files' => array(__FILE__),
		'tags' => 'tag-test',
		'priority' => 6,
	));
}


echo debug::renderToolbar();
