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

	protected $htmlTag = 'textarea';

	public function prepareControl()
	{
		parent::prepareControl();
		$this->control->setText($this->getHtmlValue());
	}

}


class FormHiddenControl extends FormInputControl
{

	protected $htmlType = 'hidden';
	protected $htmlTypeClass = '';

	public function __construct(Form $form, $name)
	{
		parent::__construct($form, $name, false);
	}

}


class FormTextControl extends FormInputControl
{

	protected $filters = array('trim');
	protected $htmlType = 'text';
	protected $htmlTypeClass = 'text';

}


class FormPasswordControl extends FormInputControl
{

	protected $htmlType = 'password';
	protected $htmlTypeClass = 'text';


	protected function getHtmlValue()
	{
		return '';
	}


}