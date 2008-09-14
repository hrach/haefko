<?php



require_once '../../../Haefko/Form.php';
require_once '../../../Haefko/Debug.php';



$form = new Form('form-multi.php', false);
$form->addMultiCheckbox('spotrebice', array('pc' => 'PC', 'dvd' => 'DVD', 'bluray' => 'Blu-Ray'))
	 ->addMultiSelect('spotrebice2', array('pc' => 'PC', 'dvd' => 'DVD', 'bluray' => 'Blu-Ray'))
	 ->addSubmit();


$form->setDefaults(array('spotrebice' => array('pc', 'bluray', 'dvd')));
$form->setDefaults(array('spotrebice2' => array('pc', 'bluray')));


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
	label.error, label.checkbox {
		width: auto;
	}
	label.error {
		color: red;
	}
	div.submit input {
		margin-left: 162px;
	}
</style>

<hr />

<?= $form->render('table') ?>