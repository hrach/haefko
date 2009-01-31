<?php

require_once '../../haefko/libs/form.php';
require_once '../../haefko/libs/debug.php';




$form = new Form();
$form->addFile('photo')
	 ->addSubmit();

if ($form->isSubmit() && $form->isValid()) {

	echo "<h1>Odeslano:</h1>";
	Debug::dump($form->data);
	$file = $form->data['photo'];
	if ($file->ok())
		$file->move("./test.jpeg");

}


// ======== html render ========


?>

<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />

<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="jquery.validate.js"></script>
<link rel="stylesheet" href="style.css" type="text/css" />
<hr />


<?= $form->render() ?>