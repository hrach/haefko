<?php

require_once '../../Haefko/Form.php';
require_once '../../Haefko/Debug.php';



$form = new Form('form.php', false);
$form->addText('name')
	 ->addText('url')
	 ->addPassword('pass')
	 ->addPassword('pass_check')
	 ->addCheckbox('agree')
	 ->addSelect('city', array('Praha', 'Brno', 'Ostrava'))
	 ->addSubmit()
	 ->addReset();

$form['url']->setEmptyValue('http://');
$form['url']->addCondition(Form::FILLED)->addRule(Form::URL, 'Zadejte validní URL');
$form['pass']->addRule(Form::MINLENGTH, 'Prilis krastké heslo', 4)->addRule(Form::EQUAL, 'Hesla musi byt stejna', $form['pass_check']);
$form['agree']->addRule('filled', 'Musíte souhlasi s podmínkami smlouvy!');


$form->setDefaults(array('agree' => true));

// =============== {end of configuration} ==================


if ($form->isSubmit()) {
	if ($form->isValid()) {
		echo "<h1>Odeslano:</h1>";
		Debug::dump($form->getData());
		exit;
	} else {
		echo "<h1>Chyby:</h1>";
		echo $form->getErrorsList();
	}
}


// =============== {html render} ==================

?>

	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<script type="text/javascript" src="jquery.js"></script>
	<script type="text/javascript" src="jquery.validate.js"></script>


<hr />
<?= $form ?>

<button name="test" type="button" value="" onclick="validateform()">Button Text</button>