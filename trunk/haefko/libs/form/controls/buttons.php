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


abstract class FormButtonControl extends FormControl
{


	/**
	 * Constructor
	 * @param Form $form
	 * @param string $name control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return FormButtonControl
	 */
	public function __construct(Form $form, $name, $label)
	{
		parent::__construct($form, $name, false);
		$this->setValue($label);
	}


	/**
	 * Returns html control
	 * @return Html
	 */
	protected function getControl()
	{
		return parent::getControl()->value($this->getHtmlValue());
	}


}


class FormSubmitControl extends FormButtonControl
{


	/**
	 * Returns Html object of form control
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->setTag('input')->type('submit')->class('button');
	}


}


class FormResetControl extends FormButtonControl
{


	/**
	 * Returns Html object of form control
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->setTag('input')->type('reset')->class('button');
	}


}