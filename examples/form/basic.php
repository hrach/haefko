<?php

require_once '../../haefko/loader.php';

debug::init();
config::write('core.debug', 2);

$form = new Form();

$label_age = Html::el('label', 'Věk')->append(Html::el('small', ' (nepovinné)'));

$form->addText('name', 'Jméno')
	 ->addTextarea('aboutMe', 'O mně')
	 ->addText('age', $label_age)
	 ->addRadio('sex', aa('male', 'Muž', 'female', 'Žena'), 'Pohlaví')
	 ->addSelect('city', aa('brno', 'Brno', 'ostrava', 'Ostrava', 'praha', 'Praha'))
	 ->addPassword('password', 'Heslo')
	 ->addPassword('password2', 'Heslo znovu')
	 ->addCheckbox('agree', 'Souhlasím')
	 ->addSubmit('Register');

$form['name']->addRule(Rule::FILLED);
$form['name']->addRule(Rule::LENGTH, '>5', 'Zadejte délku větší jak 5.');
$form['age']->addCondition(Rule::FILLED)
			->addRule(Rule::INTEGER)
			->addRule(Rule::RANGE, a(15,99));
$form['sex']->addRule(Rule::FILLED);
$form['password']->addRule(Rule::EQUAL, $form['password2'], 'Hesla se musí shodovat');
$form['agree']->addRule(Rule::FILLED, null, 'Musíte souhlasit s podmínkami');

if ($form->isSubmit() && $form->isValid()) {

	echo "<h1>Odeslano:</h1>";
	Debug::dump($form->data);
	exit;

}


// ======== html render ========

?>

<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="validation/jquery.js"></script>
<script type="text/javascript" src="validation/jquery.validation.js"></script>
<link rel="stylesheet" href="style.css" type="text/css" />
<h1>Háefko forms</h1>

<?php

	$form->setRenderer('dl');
	echo $form->renderer->render('start');

	echo $form->renderer->render('part', a('name'), 'Osobní údaje');
	echo $form->renderer->render('part', a('aboutMe', 'age', 'sex'), 'Další');
	echo $form->renderer->render('part', a(), 'Odeslání');

	echo $form->renderer->render('end');

?>