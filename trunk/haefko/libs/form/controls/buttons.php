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


abstract class FormButtonControl extends FormControl
{

	public function __construct(Form $form, $name, $label)
	{
		parent::__construct($form, $name, false);
		$this->setValue($label);
	}

	protected function getControl()
	{
		return parent::getControl()->value($this->getHtmlValue());
	}

}


class FormSubmitControl extends FormButtonControl
{

	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->setTag('input')->type('submit')->class('button');
	}

}


class FormResetControl extends FormButtonControl
{

	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->setTag('input')->type('reset')->class('button');
	}

}