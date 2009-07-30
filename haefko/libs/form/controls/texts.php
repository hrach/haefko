<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
 * @subpackage  Forms
 */


class FormTextareaControl extends FormControl
{


	/**
	 * Returns Html object of form control
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->setTag('textarea');
	}


	/**
	 * Returns html control
	 * @return Html
	 */
	protected function getControl()
	{
		return parent::getControl()->setHtml($this->getHtmlValue());
	}


}


class FormHiddenControl extends FormInputControl
{


	/**
	 * Constructor
	 * @param Form $form
	 * @param string $name control name
	 * @return FormHiddenControl
	 */
	public function __construct(Form $form, $name)
	{
		parent::__construct($form, $name, false);
	}


	/**
	 * Returns Html object of form control
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->type('hidden');
	}


}


class FormTextControl extends FormInputControl
{

	/** @var array */
	protected $filters = array('trim');


	/**
	 * Returns Html object of form control
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->type('text')->class('text');
	}


}


class FormDatepickerControl extends FormInputControl
{


	/**
	 * Set the control value
	 * @param mixed $value new value
	 * @return bool
	 */
	public function setValue($value)
	{
		if (!empty($value))
			$value = date('Y-m-d', strtotime($value));

		parent::setValue($value);
	}


	/**
	 * Returns value for html tag
	 * @return string
	 */
	public function getHtmlValue()
	{
		$value = parent::getHtmlValue();
		$value = preg_replace('#([0-9]{4})-([0-9]{2})-([0-9]{2})#', '$3.$2.$1', $value);
		return $value;		
	}


	/**
	 * Returns Html object of form control
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->class('calendar');
	}


}


class FormPasswordControl extends FormInputControl
{


	/**
	 * Returns Html object of form control
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		return $control->type('password')->class('text');
	}


	/**
	 * Returns value for html tag
	 * @return string
	 */
	protected function getHtmlValue()
	{
		return '';
	}


}