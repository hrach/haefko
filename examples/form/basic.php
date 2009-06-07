<?php


require_once '../../haefko/libs/config.php';
require_once '../../haefko/libs/form.php';
require_once '../../haefko/libs/debug.php';

config::write('core.debug', 2);

$form = new Form();

$label_age = Html::el('label', 'Věk');
$label_age->append(Html::el('small', ' (nepovinné)'));

$form->addText('name', 'Jméno')
	 ->addTextarea('aboutMe', 'O mně')
	 ->addText('age', $label_age)
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

$form['password']->addRule(Form::EQUAL, $form['password2'], 'Hesla se musí shodovat');
$form['agree']->addRule(Form::FILLED, null, 'Musíte souhlasit s podmínkami');

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
<h1>Háefko forms</h1>

<?php

	$form->renderer('div');
	//echo $form->render();
	//exit;
	echo $form->render('start');

	echo $form->render('part', a('name'), 'Osobní údaje');
	echo $form->render('part', a('aboutMe', 'age', 'sex'), 'Další');
	echo $form->render('part', a(), 'Odeslání');

	echo $form->render('end');

?>