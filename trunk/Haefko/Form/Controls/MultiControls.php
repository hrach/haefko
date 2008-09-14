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



class FormMultipleSelectControl extends FormControl
{

	protected $htmlTag = 'select';
	protected $options = array();
	protected $values = array();

	public function __construct($form, $name, $options, $label = null)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;

		foreach ($this->options as $key => $option) {
			if (is_array($option)) {
				foreach (array_keys($option) as $val)
					$this->values[] = $val;
			} else {
				$this->values[] = $key;
			}
		}
	}

	public function setValue($value)
	{
		foreach ((array) $value as $key) {
			if (!in_array($key, $this->values))
				return false;
		}

		$this->value = $value;
	}

	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->name .= '[]';
		$this->control->multiple = 'multiple';
		$this->control->setContent($this->getOptions(), false);
	}

	protected function getOptions()
	{
		$html = null;
		foreach ($this->options as $name => $value) {

			if (is_array($value)) {
				$html .= "\n<optgroup label=\"$name\">\n";
				foreach ($value as $key => $opt)
					$html .= $this->getOption($key, $opt);
				$html .= "</optgroup>";
			} else {
				$html .= $this->getOption($name, $value);
			}

		}

		return $html;
	}

	protected function getOption($name, $value)
	{
		$el = Html::el('option', $value);
		$el->value = $name;

		if (in_array($name, (array) $this->getControlValue()))
			$el->selected = 'selected';

		return $el->render();
	}

}


class FormMultiCheckboxControl extends FormControl
{

	protected $options = array();

	public function __construct($form, $name, $options)
	{
		parent::__construct($form, $name);
		$this->options = $options;
	}

	public function control($attrs = array())
	{
		$render = '';

		$id = $this->control->id;
		$label = Html::el('label');
		$label->class = 'checkbox';
		$el = Html::el('input');
		$el->type = 'checkbox';
		$el->name = $this->control->name . '[]';

		foreach ($this->options as $key => $value) {
			$el->value = $key;
			$el->id = "$id-$key";
			if (in_array($key, $this->getControlValue()))
				$el->checked = 'checked';

			$label->for = "$id-$key";
			$label->id = "$id-$key-label";
			$label->setContent($value);

			$html .= $el->render() . $label->render();
		}

		return $html;
	}



}


