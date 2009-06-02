<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Forms
 */


class FormTextareaControl extends FormControl
{

	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->setTag('textarea');
	}

	protected function getControl()
	{
		return parent::getControl()->setHtml($this->getHtmlValue());
	}

}


class FormHiddenControl extends FormInputControl
{

	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->type('hidden');
	}


	public function __construct(Form $form, $name)
	{
		parent::__construct($form, $name, false);
	}

}


class FormTextControl extends FormInputControl
{

	protected $filters = array('trim');

	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->type('text')->class('text');
	}

}

class FormDatepickerControl extends FormInputControl
{

	public function setValue($value)
	{
		dump($value);
		if (!empty($value))
			$value = date('Y-m-d', strtotime($value));

		parent::setValue($value);
	}
	
	
	public function getHtmlValue()
	{
		$value = parent::getHtmlValue();
		$value = preg_replace('#([0-9]{4})-([0-9]{2})-([0-9]{2})#', '$3.$2.$1', $value);

		return $value;		
	}

	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->class('calendar');
	}

}

class FormPasswordControl extends FormInputControl
{
	
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->type('password')->class('text');
	}


	protected function getHtmlValue()
	{
		return '';
	}


}