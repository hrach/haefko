<?php

# loader haefko libraries
require_once '../../haefko/loader.php';
debug::init(true);


$template = new Template('template.tpl');
$template->variable = 'obsah a <tag> s .';
$template->bool = true;

echo $template->render();
echo debug::renderToolbar();