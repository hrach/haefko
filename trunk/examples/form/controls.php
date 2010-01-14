<?php

require 'haefko/loader.php';
Debug::init(true);

$multi = array(
	'desk' => 'Desk',
	'pencil' => 'Pencil',
	'tv' => 'Televesion',
	'pc' => 'Computer',
);

$form = new Form();
$form->addText('text-field');
$form->addTextarea('textarea');
$form->addMultiCheckbox('multi-checks', $multi);
$form->addMultiSelect('multi-select', $multi);
$form->addSelect('select', $multi);
$form->addSubmit();

$form['select']->setEmptyValue('-- select value --');
if ($form->isSubmit())
	dump($form->data);

echo $form;