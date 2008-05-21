<?php



require_once 'hf/HF/Forms/Form.php';
require_once 'hf/HF/Debug.php';



$form = new Form('form.php');
$form->addText('url')
	 ->addPassword('pass')
	 ->addSelect('city', array('Praha', 'Brno'))
	 ->addFile('file')
	 ->addCheckbox('agree')
	 ->addHidden('hide')
	 ->addSubmit();

$form['url']->setEmptyValue('http://');
$form['url']->addRule(Form::FILLED, 'Vyplň url')->addRule(Form::URL, 'Zadejte validní URL');
$form['pass']->addRule(Form::MINLENGTH, 'Prilis krastké heslo', 4);
$form['agree']->addRule(Form::EQUAL, 'Musíte souhlasit!', true);
$form['city']->addRule(Form::FILLED, 'Vyplň město');


$form->setDefaults(array('hide' => 'secret'));

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
<fieldset>
<legend>Ukázkový formlulář</legend>
	<?= $form->start() ?>

	<table>
		<tr>
			<td><?= $form['url']->label('Name') ?></td>
			<td><?= $form['url']->element() ?></td>
		</tr>
		<tr>
			<td><?= $form['pass']->label('Heslo') ?></td>
			<td><?= $form['pass']->element() ?></td>
		</tr>
		<tr>
			<td><?= $form['city']->label('Město') ?></td>
			<td><?= $form['city']->element() ?></td>
		</tr>
		<tr>
			<td><?= $form['file']->label('Soubor') ?></td>
			<td><?= $form['file']->element() ?></td>
		</tr>
		<tr>
			<td></td>
			<td><?= $form['agree']->element() ?><?= $form['agree']->label('Souhlasím') ?></td>
		</tr>
		<tr>
			<td></td>
			<td><?= $form['submit']->element('Poslat') ?></td>
		</tr>
	</table>

	<?= $form->end() ?>

</fieldset>