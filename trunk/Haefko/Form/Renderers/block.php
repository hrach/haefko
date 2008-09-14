<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.8
 * @package     Haefko
 */


class FormBlockRenderer implements IFormRenderer
{


	public function start(Form $form)
	{}


	public function end(Form $form)
	{}

	public function control(FormControl $control)
	{
		$render = '<div';
		$classes = array();

		if ($control->htmlRequired)
			$classes[] = 'required';

		if ($control->htmlTag == 'input')
			$classes[] = $control->htmlType;

		if (!empty($classes))
			$render .= ' class="' . implode(' ', $classes) . '"';

		$render .= '>'
		         . $control->label()
		         . $control->control()
		         . '<div class="errors">' . $control->errors() . '</div>'
		         . "</div>\n";

		$control->increment();
		return $render;
	}


}