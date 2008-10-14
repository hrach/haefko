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


/**
 * Textarea
 */
class FormTextareaControl extends FormContaineredControl
{

	protected $htmlTag = 'textarea';

	public function prepareControl()
	{
		parent::prepareControl();
		$this->control->setText($this->getHtmlValue());
	}

}


/**
 * Input hidden
 */
class FormHiddenControl extends FormInputControl
{

	protected $htmlType = 'hidden';
	protected $htmlTypeClass = '';

	public function __construct(Form $form, $name, $label)
	{
		parent::__construct($form, $name, false);
	}

}


/**
 * Input text
 */
class FormTextControl extends FormInputContaineredControl
{

	protected $filters = array('trim');
	protected $htmlType = 'text';
	protected $htmlTypeClass = 'text';

}


/**
 * Input password
 */
class FormPasswordControl extends FormInputContaineredControl
{

	protected $htmlType = 'password';
	protected $htmlTypeClass = 'text';

}