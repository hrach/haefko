<?php

require 'haefko/loader.php';
debug::init(true);
config::write('db.log', true);


Db::connect(array('database' => 'haefko_examples'));

$query = Db::prepare('
select * from [artists]
left join [albums] on [albums.artist_id] = [artists.id]
');

$query->setAssociation('artists', 'albums');
$query->execute();


echo "<ul>";
foreach ($query as $entry) {
	echo "<li>" . $entry->artists->name;
	if (!empty($entry->albums)) {
		echo "<ul>";
		foreach ($entry->albums as $album) {
			echo "<li>" . $album->name . "</li>";
		}
		echo "</ul>";
	}
	echo "</li>";
}
echo "</ul>";


echo "<hr />";
# ==============================================================================


$query = Db::prepare('
select * from [artists]
left join [albums] on [albums.artist_id] = [artists.id]
left join [partners] on [artists.partner_id] = [partners.id]
');

$query->setAssociation('artists', 'albums');
$query->execute();


echo "<ul>";
foreach ($query->fetchAll() as $entry) {
	echo "<li>" . $entry->artists->name;
	if (!empty($entry->partners->id))
		echo "<br> Partner: " . $entry->partners->name;

	if (!empty($entry->albums)) {
		echo "<br />Albums: <ul>";
		foreach ($entry->albums as $album) {
			echo "<li>" . $album->name . "</li>";
		}
		echo "</ul>";
	}
	echo "</li>";
}
echo "</ul>";