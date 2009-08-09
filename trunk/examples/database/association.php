<?php

require 'haefko/loader.php';
debug::init(true);


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


# ==============================================================================


$query = Db::prepare('
select * from [artists]
');