<?php

require_once '../../../Haefko/Form.php';
require_once '../../../Haefko/Debug.php';


$form = new Form();

$form->addText('text', 'Jméno')
	 ->addText('vlastnosti[]')
	 ->addSubmit('Upload user');


$form['vlastnosti']->addRule(~Form::EQUAL, 'pracovitost');


if ($form->isSubmit() && $form->isValid()) {

	echo "<h1>Odeslano:</h1>";
	Debug::dump($form->data);

}


// ======== html render ========

?>

<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="jquery.validate.js"></script>
<style type="text/css" media="screen">
	label {
		display: inline-block;
		width: 150px;
		padding: 4px;
		text-align: right;
	}
	label.error {
		width: auto;
		color: red;
	}
	div.submit input {
		margin-left: 162px;
	}
</style>

<hr />


<?= $form->startTag() ?>

<?= $form['text']->block ?>
<?= $form['vlastnosti']->block ?>
<?= $form['vlastnosti']->block ?>

<?= $form['nadvlastnosti']->block ?>
<?= $form['nadvlastnosti']->block ?>

<?= $form['submit']->block ?>
<?= $form->endTag() ?>