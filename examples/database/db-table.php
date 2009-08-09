<?php

require 'haefko/loader.php';
Debug::init(true);
Db::connect(array('database' => 'haefko_examples'));
$class = DbTable::init('Albums');


echo "<h2>reading get</h2>";
$person = new $class(3);
dump($person->get());


echo "<h2>reading getby</h2>";
$album = new $class;
$album->setName('Test album - ' . date('d.m.Y'));
dump($album->getBy('name'));


echo "<h2>writing</h2>";
if (isset($_GET['add'])) {
	$album = new $class;
	$album->setName("Test album - " . date('d.m.Y'))
	      ->setArtistId(1)
	      ->save();

	header('location: db-table.php');
} else {
	echo "<a href='?add'>Vlozit zaznam</a>";
}