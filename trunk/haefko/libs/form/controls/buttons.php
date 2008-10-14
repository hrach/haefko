<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko
 */


abstract class FormButtonControl extends FormControl
{

	protected $htmlTag = 'input';

	public function __construct(Form $form, $name, $label)
	{
		parent::__construct($form, $name, false);
		$this->setValue($label);
	}

	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->value = $this->getHtmlValue();
		$this->control->type = $this->htmlType;
		$this->control->class = $this->htmlTypeClass;
	}

}


class FormSubmitControl extends FormButtonControl
{

	protected $htmlType = 'submit';
	protected $htmlTypeClass = 'button';

}


class FormResetControl extends FormButtonControl
{

	protected $htmlType = 'reset';
	protected $htmlTypeClass = 'button';

}