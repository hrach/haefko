<?php

require_once 'haefko/loader.php';
debug::init(true);

$data = array(1,2,3,5,6,7);


$form = new Form();
$form->addRadio('radio', array_flip($data));

//echo $form;

echo $form->startTag();
echo $form['radio']->label();
echo "<table border=1>";
foreach ($data as $i) {
	echo "<tr><td>";
	echo $form['radio']->getControl($i);
	echo "</td><td>Entry $i</td></tr>";
}

echo $form->endTag();