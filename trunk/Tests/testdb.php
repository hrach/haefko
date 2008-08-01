<?php

$startTime = microtime(true);

require_once './Db/DbConnection.php';

$db = new DbConnection(array('driver' => 'mysqli', 'database' => 'hrach_blog'));
$res = $db->query("select [posts.url], [posts.id], [comments.id] from [posts] left join [comments] on [comments.post_id] = [posts.id] order by [posts.id]");


//print_r($res->fetchAll());
///print_r($res->fetch());
///print_r($res->fetch());
///print_r($res->fetch());

//$res = $res->fetch();

//echo $res->id;
//echo $res->name;

foreach ($res->fetchAll() as $val) {
	echo $val->posts->id . " - " . $val->posts->url . " - ";
	foreach ($val->comments as $comment) {
		echo $comment->id;
	}
	echo "<br>";
}


echo "<br>";
echo round((microtime(true) - $startTime) * 1000, 2);