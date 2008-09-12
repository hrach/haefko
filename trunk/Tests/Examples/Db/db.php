<?php

$startTime = microtime(true);

require_once '../../../Haefko/Debug.php';
require_once '../../../Haefko/Db.php';

db::connect(array('database' => 'hrach_blog'));

$query = db::query("select posts.title from posts order by RAND() limit 1");
echo $query->fetchField();

echo "<hr />";

$query = db::query("select posts.title, posts.id from posts order by title limit 10");
echo $query->dump();

echo "<hr />";

$query = db::query("select posts.title, comments.id, posts.id from posts left join comments on comments.post_id = posts.id where posts.id = 47 or posts.id = 1 order by title limit 10");
Debug::dump($query->fetchAll('posts'));

echo "<hr />";

$query = db::query("select posts.id, posts.title from posts order by title limit 10");
Debug::dump($query->fetchPairs());


//$query->fetchField();
//$query->fetchPairs();
//$query->fetchRow();
//$query->fetchAll();

echo "<br>";
echo round((microtime(true) - $startTime) * 1000, 2);