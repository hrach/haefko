<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Forms
 */


class FormCheckboxControl extends FormInputControl
{

	protected $htmlType = 'checkbox';
	protected $htmlTypeClass = 'checkbox';

	public function setValue($value)
	{
		$this->value = (bool) $value;
	}

	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->value = null;
		if ($this->getValue())
			$this->control->checked = 'checked';
	}

}


class FormMultiCheckboxControl extends FormControl
{

	protected $options = array();

	public function __construct($form, $name, $options, $label)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;
	}

	public function control($attrs = array())
	{
		$this->htmlRendered = true;

		$s = '';
		$label = Html::el('label');
		$el = Html::el('input', null, array('type' => 'checkbox', 'class' => 'checkbox', 'name' => $this->control->name . '[]'));
		foreach ($this->options as $key => $value) {
			$el->value($key)
			   ->id("{$this->htmlId}-$key")
			   ->checked(in_array($key, (array) $this->getHtmlValue()) ? 'checked' : null);

			$label->for("{$this->htmlId}-$key")
				  ->id("{$this->htmlId}-$key-label")
				  ->clearContent()
				  ->setText($value);

			$s .= $el->render() . $label->render() . '<br />';
		}

		return $s;
	}


}