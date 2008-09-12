<?php

require_once '../../../Haefko/Form.php';
require_once '../../../Haefko/Debug.php';


$form = new Form();

$form->addText('name', 'Jméno')
	 ->addTextarea('aboutMe', 'O mě')
	 ->addText('age', 'Věk')
	 ->addRadio('sex', aa('male', 'Muž', 'female', 'Žena'), 'Pohlaví')
	 ->addSelect('city', aa('brno', 'Brno', 'ostrava', 'Ostrava', 'praha', 'Praha'))
	 ->addPassword('password', 'Heslo')
	 ->addPassword('password2', 'Heslo znovu')
	 ->addCheckbox('agree', 'Souhlasím')
	 ->addSubmit('Register');

$form['name']->addRule(~Form::INARRAY, a('petr', 'pepa'));
$form['name']->addRule(Form::FILLED);
$form['age']->addCondition(Form::FILLED)
			->addRule(Form::NUMERIC)
			->addRule(Form::RANGE, a(15,99));

$form['password']->addRule(Form::EQUAL, $form['password2']);

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
	label.error, label.radio {
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

<?= $form ?>