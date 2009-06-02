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


class FormSelectControl extends FormControl
{

	protected $options = array();
	protected $values = array();

	public function __construct($form, $name, $options, $label = null)
	{
		$this->options = $options;
		foreach ($this->options as $key => $option) {
			if (is_array($option)) {
				foreach (array_keys($option) as $val)
					$this->values[] = $val;
			} else {
				$this->values[] = $key;
			}
		}
		
		parent::__construct($form, $name, $label);
	}

	public function setValue($value)
	{
		if (!$this->isAllowedValue($value))
			return false;

		parent::setValue($value);
	}

	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->setTag('select')
		                               ->onfocus("this.onmousewheel=function(){return false}");
	}

	protected function getControl()
	{
		return parent::getControl()->setHtml($this->getOptions());
	}

	protected function isAllowedValue($value)
	{
		if (!in_array($value, $this->values))
			return false;

		return true;
	}

	protected function getOptions()
	{
		$options = Html::el();
		foreach ($this->options as $key => $val) {
			if (is_array($val)) {

				$optgroup = Html::el('optgroup');
				$optgroup->label($name);
				foreach ($value as $subKey => $subVal)
					$optgroup->addHtml($this->getOption($subKey, $subVal));

				$options->addHtml($optgroup);
			} else {

				$options->addHtml($this->getOption($key, $val));
			}
		}

		return $options;
	}

	protected function getOption($name, $value)
	{
		return Html::el('option', $value, array(
			'value' => $name,
			'selected' => $this->getHtmlValue() == $name
		));
	}

}



class FormMultipleSelectControl extends FormSelectControl
{

	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl()->multiple(true);
		$control->name .= '[]';
		return $control;
	}

	public function setValue($value)
	{
		foreach ((array) $value as $key) {
			if (!in_array($key, $this->values))
				return false;
		}

		$this->value = $value;
	}

	protected function getOption($name, $value)
	{
		return Html::el('option', $value, array(
			'value' => $name,
			'selected' => in_array($name, (array) $this->getHtmlValue())
		));
	}

}
