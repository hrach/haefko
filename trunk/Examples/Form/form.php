<?php

require_once '../../Haefko/Form.php';
require_once '../../Haefko/Debug.php';



$form = new Form('form.php', false);
$form->addText('name')
	 ->addText('url')
	 ->addPassword('pass')
	 ->addPassword('pass_check')
	 ->addSelect('city', array('Praha', 'Brno', 'Ostrava'))
	 ->addSubmit();

$form['url']->setEmptyValue('http://');
$form['url']->addCondition(Form::FILLED)->addRule(Form::URL, 'Zadejte validní URL');
$form['pass']->addRule(Form::MINLENGTH, 'Prilis krastké heslo', 4)->addRule(Form::EQUAL, 'Hesla musi byt stejna', $form['pass_check']);


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


<hr />
<?= $form ?>