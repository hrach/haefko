<?php

require_once 'haefko/loader.php';
Debug::init(true);



class Text extends Object
{
	public $names = array();

	public function add($name)
	{
		$this->names[] = $name;
	}
}

function printTextNames($_this, $separator = ", ")
{
	return implode($separator, $_this->names);
}


Text::extendMethod('text::printall', 'printtextnames');


$names = new Text();
$names->add('Jan Skrasek');
$names->add('Petr Pribyl');
$names->add('Martin Omacka');

echo $names->printall();