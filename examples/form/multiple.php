<?php

require_once '../../haefko/libs/form.php';
require_once '../../haefko/libs/config.php';
require_once '../../haefko/libs/debug.php';

config::write('core.debug', 3);


$form = new Form();
$form->addMultiCheckbox('spotrebice', array('pc' => 'PC', 'dvd' => 'DVD', 'bluray' => 'Blu-Ray'),"Spotřebiče")
     ->addMultiSelect('spotrebice2', array('pc' => 'PC', 'dvd' => 'DVD', 'bluray' => 'Blu-Ray'),"Mé druhé spotřebiče")
     ->addSubmit();


$form->setDefaults(array('spotrebice' => array('pc', 'bluray', 'dvd')));
$form->setDefaults(array('spotrebice2' => array('pc', 'bluray')));


if ($form->isSubmit() && $form->isValid()) {

    echo "<h1>Odeslano:</h1>";
    Debug::dump($form->data);

}

$form->setRenderer('dl');


// ======== html render ========


?>

<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="jquery.validate.js"></script>
<link rel="stylesheet" href="style.css" type="text/css" />

<h1>Multiple Form</h1>

<?= $form ?>