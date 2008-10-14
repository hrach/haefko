<?php

require_once '../../haefko/libs/form.php';
require_once '../../haefko/libs/debug.php';

$form = new Form();
$form->addText('username')
	 ->addPassword('contats[]')
	 ->addSubmit('Add users');

$form['contats']->addRule(Form::INARRAY, a('icq', 'email', 'jabber'));

if ($form->isSubmit() && $form->isValid()) {

	echo "<h1>Odeslano:</h1>";
	Debug::dump($form->data);
	exit;

}


// ======== html render ========

?>

<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="jquery.validate.js"></script>
<link rel="stylesheet" href="style.css" type="text/css" />


<h1>Containered Form</h1>

<?php

	echo $form->render('start');
	echo $form->render('body', 'Uživatel', a('username'));

	echo $form->renderer->body->startTag();
	for ($i = 0; $i < 3; $i++)
		echo $form->render('block', 'contats');
	echo $form->renderer->body->endTag();

	echo $form->render('body');
	echo $form->render('end');