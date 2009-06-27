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


	/** @var array */
	protected $options = array();

	/** @var array - Options without tree structure */
	protected $values = array();


	/**
	 * Constructor
	 * @param   Form     form
	 * @param   string   control name
	 * @param   mixed    label (null = from name, false = no label)
	 * @return  void
	 */
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


	/**
	 * Set the control value
	 * @param   mixed   new value
	 * @return  bool
	 */
	public function setValue($value)
	{
		if (!$this->isAllowedValue($value))
			return false;

		parent::setValue($value);
	}


	/**
	 * Returns Html object of form control
	 * @return  Html
	 */
	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->setTag('select')->onfocus("this.onmousewheel=function(){return false}");
	}


	/**
	 * Returns html control
	 * @return  Html
	 */
	protected function getControl()
	{
		return parent::getControl()->setHtml($this->getOptions());
	}


	/**
	 * Returns treu if is the value allowed
	 * @param   string   value
	 * @return  bool
	 */
	protected function isAllowedValue($value)
	{
		if (!in_array($value, $this->values))
			return false;

		return true;
	}


	/**
	 * Returns html options tags
	 * @return  string
	 */
	protected function getOptions()
	{
		$options = Html::el();

		if ($this->emptyValue != '') {
			$options->addHtml($this->getOption('', $this->emptyValue));
		}

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


	/**
	 * Returns option control
	 * @param   string    control name
	 * @param   string    control value
	 * @return  Html
	 */
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


	/**
	 * Set the control value
	 * @param   mixed   new value
	 * @return  bool
	 */
	public function setValue($value)
	{
		foreach ((array) $value as $key) {
			if (!in_array($key, $this->values))
				return false;
		}

		$this->value = $value;
	}


	/**
	 * Returns Html object of form control
	 * @return  Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl()->multiple(true);
		$control->name .= '[]';
		return $control;
	}


	/**
	 * Returns option control
	 * @param   string    control name
	 * @param   string    control value
	 * @return  Html
	 */
	protected function getOption($name, $value)
	{
		return Html::el('option', $value, array(
			'value' => $name,
			'selected' => in_array($name, (array) $this->getHtmlValue())
		));
	}


}
