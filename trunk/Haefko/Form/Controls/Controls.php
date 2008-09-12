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

	protected $tag = 'input';

	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->value = $this->getControlValue();
		$this->control->type = $this->type;
	}

}


class FormCheckboxControl extends FormInputControl
{

	protected $type = 'checkbox';

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

	protected $type = 'hidden';

	public function __construct(Form $form, $name, $label)
	{
		parent::__construct($form, $name, false);
	}

}


abstract class FormButtonControl extends FormControl
{

	protected $tag = 'input';

	public function __construct(Form $form, $name, $label)
	{
		parent::__construct($form, $name, false);
		$this->setValue($label);
	}

	public function label()
	{
		return null;
	}

	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->value = $this->getControlValue();
		$this->control->type = $this->type;
	}

}


class FormSubmitControl extends FormButtonControl
{

	protected $classes = array('submit');
	protected $type = 'submit';

}


class FormResetControl extends FormButtonControl
{

	protected $classes = array('reset');
	protected $type = 'reset';

}


class FormRadioControl extends FormInputControl
{

	protected $type = 'radio';
	protected $classes = array('radio');
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

		$id = $this->control->id;
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
