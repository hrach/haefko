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


class FormCheckboxControl extends FormInputControl
{


	/**
	 * Set the control value
	 * @param   mixed   new value
	 * @return  bool
	 */
	public function setValue($value)
	{
		$this->value = (bool) $value;
	}


	/**
	 * Returns Html object of form control
	 * @return  Html
	 */
	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->type('checkbox')->class('checkbox');
	}


	/**
	 * Returns html control
	 * @return  Html
	 */
	protected function getControl()
	{
		return parent::getControl()->value(null)->checked($this->value);
	}


}


class FormMultiCheckboxControl extends FormInputControl
{


	/** @var string - Control separator */
	public $listSeparator = '<br />';

	/** @var array */
	protected $options = array();


	/**
	 * Constructor
	 * @param   Form     form
	 * @param   string   control name
	 * @param   mixed    label (null = from name, false = no label)
	 * @return  void
	 */
	public function __construct($form, $name, $options, $label)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;
	}


	/**
	 * Set the control value
	 * @param   mixed   new value
	 * @return  bool
	 */
	public function setValue($value)
	{
		foreach ((array) $value as $key) {
			if (!isset($this->options[$key]))
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
		$control = parent::getHtmlControl()->type('checkbox')->class('checkbox');
		$control->name .= '[]';
		return $control;
	}


	/**
	 * Returns html control
	 * @return  Html
	 */
	public function getControl()
	{
		$label = Html::el('label');
		$control = parent::getControl();
		$container = Html::el('div')->id($control->id)->class('multi-inputs');

		$i = 0;
		$id = $control->id;
		foreach ($this->options as $key => $val) {
			$i++;

			$control->id = $id . '-' . $key;
			$control->value = $key;
			$control->checked = in_array($key, (array) $this->getHtmlValue());

			$label->for = $id . '-' . $key;
			$label->setText($val);

			$container->addHtml($control->render()
			                  . $label->render()
			                  . ($this->listSeparator instanceof Html ? $this->listSeparator->render() : $this->listSeparator));
		}

		return $container;
	}


}