<?php

require_once '../Haefko/Form.php';
require_once '../Haefko/Debug.php';



$form = new Form('form.php');
$form->addText('name', 'Jméno')
	 ->addText('url', 'Web');

/*
	 ->addPassword('pass', 'Heslo')
	 ->addPassword('pass_chechk', 'Heslo znovu')
	 ->addSingleCheckbox('agree', 'Souhlasím se smlouvou')
	 ->addSelect('city', array('Mesta' => array('Praha', 'Brno', 'Ostrava')))
*/
$form->addSubmit('Nahrat');

//	 ->addReset('Resetovat');

$form['url']->emptyValue = 'http://';
$form['url']->addCondition('url', 'filled')
            ->addRule('url', 'url', 'Zadej validní url');

/*
$form['pass']->addRule(Form::MINLENGTH, 'Příliš krátké heslo! %d', 4);
$form['pass_check']->addRule(Form::EQUAL, 'Hesla musí být stejná', $form['pass']);
$form['agree']->addRule(FORM::CHECK, 'Musíte souhlasit s podmínkami');

$form->setDefaults(array('agree' => true));
*/


// =============== {end of configuration} ==================


if ($form->isSubmit()) {
	if ($form->isValid()) {
		echo "<h1>Odeslano:</h1>";
		Debug::dump($form->data);
		exit;
	} else {
		echo "<h1>Chyby:</h1>";
		echo $form->errorList();
	}
}


// =============== {html render} ==================

?>

<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="jquery.validate.js"></script>

<hr />
<?= $form->start() ?>

	<?= $form['name']->block ?>
	<?= $form['url']->block ?>
	<?= $form['pass']->block ?>
	<?= $form['pass_check']->block ?>
	<?= $form['agree']->block ?>

<?= $form->end() ?>