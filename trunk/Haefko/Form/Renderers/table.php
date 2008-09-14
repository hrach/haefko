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


class FormTableRenderer implements IFormRenderer
{


	public function start(Form $form)
	{
		return '<table>';
	}


	public function end(Form $form)
	{
		return '</table>';
	}

	public function control(FormControl $control)
	{
		$render = '<tr';
		$classes = array();

		if ($control->htmlRequired)
			$classes[] = 'required';

		if ($control->htmlTag == 'input')
			$classes[] = $control->htmlType;

		if (!empty($classes))
			$render .= ' class="' . implode(' ', $classes) . '"';

		$render .= '>'
				 . '<td>' . $control->label() . '</td>'
				 . '<td>' . $control->control() . '</td>'
				 . '<td><div class="errors">' . $control->errors() . '</div></td>'
				 . "</tr>\n";

		$control->increment();
		return $render;
	}


}