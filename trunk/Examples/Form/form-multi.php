<?php



require_once '../../Haefko/Forms/Form.php';
require_once '../../Haefko/Debug.php';



$form = new Form('form-multi.php', false);
$form->addMultiCheckbox('spotrebice', array('pc' => 'PC', 'dvd' => 'DVD', 'bluray' => 'Blu-Ray'))
	 ->addMultiSelect('spotrebice2', array('pc' => 'PC', 'dvd' => 'DVD', 'bluray' => 'Blu-Ray'))
	 ->addRadio('spotrebic', array('lednice' => 'Lednice', 'mycka' => 'Myčka', 'pracka' => 'Pračka'))
	 ->addSubmit();

$form->setDefaults(array('spotrebice' => array('pc', 'bluray', 'dvd')));
$form->setDefaults(array('spotrebice2' => array('pc', 'bluray')));
$form->setDefaults(array('spotrebic' => 'mycka'));


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

<!--

<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
<fieldset>
<legend>Ukázkový formlulář</legend>
	<?= $form->start() ?>

	<table>
		<tr>
			<td></td>
			<td><?= $form['spotrebice']->render() ?></td>
		</tr>
		<tr>
			<td><?= $form['spotrebice2']->label('Test2') ?></td>
			<td><?= $form['spotrebice2']->element() ?></td>
		</tr>
		<tr>
			<td></td>
			<td><?= $form['spotrebic']->render() ?></td>
		</tr>
		<tr>
			<td></td>
			<td><?= $form['submit']->element('Poslat') ?></td>
		</tr>
	</table>

	<?= $form->end() ?>
</fieldset>

-->

<?= $form ?>