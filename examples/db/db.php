<?php

$startTime = microtime(true);

require_once '../../haefko/libs/debug.php';
require_once '../../haefko/libs/db.php';

db::connect(array('database' => 'hrach_blog'));


$a = array(
	'test%r' => 'Now()',
	'tests%s' => 'SuperTest\'',
	'tsadst' => true
);
Debug::dump(db::getConnection()->escapeArray($a));



$query = db::query("select post.title from post order by RAND() limit 1");
Debug::dump($query);
echo $query->fetchField();

echo "<hr />";

$query = db::query("select post.title, post.id from post order by title limit 10");
Debug::dump($query->fetchAll());

echo "<hr />";

$query = db::query("select post.title, comment.id, post.id from post left join comment on comment.post_id = post.id where post.id = 47 or post.id = 1 order by title limit 10");
Debug::dump($query->fetchAll('post'));

echo "<hr />";

$query = db::query("select post.id, post.title from post order by title limit 10");
Debug::dump($query->fetchPairs());

echo "<hr />";

$query = db::query("select post.title from post order by title limit 10");
Debug::dump($query->fetchPairs());


//$query->fetchField();
//$query->fetchPairs();
//$query->fetchRow();
//$query->fetchAll();

echo "<br>";
echo round((microtime(true) - $startTime) * 1000, 2);