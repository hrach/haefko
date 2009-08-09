<?php

require_once 'haefko/loader.php';
Debug::init(true);
Db::connect(array('database' => 'haefko_examples'));


echo '<h2>escaping</h2>';
$a = array(
	'test%r' => 'Now()',
	'tests%s' => 'SuperTest\'',
	'tsadst' => true
);
Debug::dump(db::getConnection()->escapeArray($a));


echo '<h2>fetch field</h2>';
dump(Db::fetchField("select [name] from [albums] order by RAND() limit 1"));


echo '<h2>fetch all</h2>';
dump(Db::fetchAll("select * from [albums] order by [name] limit 3"));


echo '<h2>fetch pairs</h2>';
dump(Db::fetchPairs("select [id], [name] from [albums] order by RAND() limit 10"));


echo '<h2>fetch pairs</h2>';
dump(Db::fetchPairs("select [name] from [albums] order by RAND() limit 10"));