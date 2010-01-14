<?php

ob_start();
require_once '../../haefko/libs/config.php';
require_once '../../haefko/libs/form.php';
require_once '../../haefko/libs/debug.php';

config::write('core.debug', 2);


$form = new Form();
$form->addDatepicker('date')
     ->addSubmit();

if ($form->isSubmit()) {
	dump($form->data);
}
     
?>


<link rel="stylesheet" media="screen" href="http://jqueryui.com/latest/themes/cupertino/ui.all.css" type="text/css" />
<script src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load("jquery", '1');
	google.load("jqueryui", '1');
	google.setOnLoadCallback(function() {
		$('.calendar').datepicker();
	});
</script>


<?php echo $form ?>

