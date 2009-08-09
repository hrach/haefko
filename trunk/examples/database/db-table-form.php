<?php

require 'haefko/loader.php';
Debug::init(true);
Db::connect(array('database' => 'haefko_examples'));


$class = DbTable::init('albums');
$albums = new $class;

$form = $albums->getForm();
$form->renderer->javascript = false;

if ($form->isSubmit() && $form->isValid())
	dump($form->data);

	
echo $form->renderer->render();