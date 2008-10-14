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


class FormSelectControl extends FormContaineredControl
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
		if ($this->container) {
			foreach ($value as $val) {
				if (!$this->isAllowedValue($val))
					return false;
			}
		} else {
			if (!$this->isAllowedValue($value))
				return false;
		}

		parent::setValue($value);
	}

	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->setHtml($this->getOptions());
	}

	protected function isAllowedValue($value)
	{
		if (!in_array($value, $this->values))
			return false;

		return true;
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

		if ($this->getHtmlValue() === $name)
			$el->selected = 'selected';

		return $el->render();
	}

}



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
		$this->control->setHtml($this->getOptions());
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

		if (in_array($name, (array) $this->getHtmlValue()))
			$el->selected = 'selected';

		return $el->render();
	}

}
