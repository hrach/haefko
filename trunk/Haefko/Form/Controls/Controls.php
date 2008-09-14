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


abstract class FormInputControl extends FormControl
{

	protected $htmlTag = 'input';

	public function getHtmlType()
	{
		return $this->htmlType;
	}

	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->value = $this->getControlValue();
		$this->control->type = $this->htmlType;
	}

}


class FormCheckboxControl extends FormInputControl
{

	protected $htmlType = 'checkbox';

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


class FormHiddenControl extends FormInputControl
{

	protected $htmlType = 'hidden';

	public function __construct(Form $form, $name, $label)
	{
		parent::__construct($form, $name, false);
	}

}


abstract class FormButtonControl extends FormControl
{

	protected $htmlTag = 'input';

	public function __construct(Form $form, $name, $label)
	{
		parent::__construct($form, $name, false);
		$this->setValue($label);
	}

	public function label()
	{
		return null;
	}

	public function getHtmlType()
	{
		return $this->htmlType;
	}

	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->value = $this->getControlValue();
		$this->control->type = $this->htmlType;
	}

}


class FormSubmitControl extends FormButtonControl
{

	protected $htmlType = 'submit';

}


class FormResetControl extends FormButtonControl
{

	protected $htmlType = 'reset';

}


class FormRadioControl extends FormInputControl
{

	protected $htmlType = 'radio';
	protected $options = array();
	protected $values = array();

	public function __construct($form, $name, $options, $label = null)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;
		$this->values = array_keys($options);
	}

	public function control($attrs = array())
	{
		$render = '';

		$id = $this->htmlId;
		$label = Html::el('label');
		$label->class = 'radio';
		$el = Html::el('input');
		$el->type = 'radio';
		$el->name = $this->control->name;

		foreach ($this->options as $key => $value) {
			$el->value = $key;
			$el->id = "$id-$key";
			if ($key == $this->getControlValue())
				$el->checked = 'checked';
			else
				$el->checked = null;

			$label->for = "$id-$key";
			$label->id = "$id-$key-label";
			$label->setContent($value);

			$html .= $el->render() . $label->render();
		}

		return $html;
	}

	protected function prepareLabel()
	{}

}
