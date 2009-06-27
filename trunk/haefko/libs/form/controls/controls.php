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


require_once dirname(__FILE__) . '/texts.php';
require_once dirname(__FILE__) . '/checkboxes.php';
require_once dirname(__FILE__) . '/selects.php';
require_once dirname(__FILE__) . '/radio.php';
require_once dirname(__FILE__) . '/file.php';
require_once dirname(__FILE__) . '/buttons.php';


abstract class FormControl extends Object
{


	/** @var string */
	protected $name;

	/** @var Form */
	protected $form;

	/** @var Html */
	protected $control;

	/** @var Html|bool */
	protected $label = false;

	/** @var mixed */
	protected $value;

	/** @var mixed */
	protected $emptyValue;

	/** @var array */
	protected $filters = array();

	/** @var string */
	protected $error;

	/** @var array */
	protected $rules = array();

	/** @var array */
	protected $conditions = array();

	/**#@+
	 * HTML elements 
	 * @var Html
	 */
	protected $htmlControl;
	protected $htmlLabel;
	protected $htmlError;
	protected $htmlErrorLabel;
	/**#@-*/

	/** @var bool - Is HTML control rendered? */
	protected $isRendered = false;

	/** @var array */
	protected $htmlRequired = false;


	/**
	 * Constructor
	 * @param   Form     form
	 * @param   string   control name
	 * @param   mixed    label (null = from name, false = no label)
	 * @return  void
	 */
	public function __construct(Form $form, $name, $label = null)
	{
		$this->form = $form;
		$this->name = $name;

		$this->htmlControl      = $this->getHtmlControl();
		$this->htmlLabel        = $this->getHtmlLabel($label);
		$this->htmlError        = $this->getHtmlError();
		$this->htmlErrorLabel   = $this->getHtmlErrorLabel();
	}

	protected function getHtmlControl()
	{
		return Html::el(null, null, array(
			'name' => $this->form->name . '[' . $this->name . ']',
			'id' => $this->form->name . '-' . $this->name
		));
	}

	protected function getHtmlLabel($label)
	{
		if ($label === false)
			return false;

		if (!($label instanceof Html))
			$label = Html::el('label', is_null($label) ? ucfirst($this->name) : $label);

		$label->for($this->form->name . '-' . $this->name)
		      ->id($this->form->name . '-' . $this->name . '-label');

		return $label;
	}
	
	protected function getHtmlError()
	{
		return Html::el('div', null, array(
			'id' => $this->form->name . '-' . $this->name . '-error',
			'class' => 'control-error'
		));
	}
	
	protected function getHtmlErrorLabel()
	{
		return Html::el('label', null, array(
			'for' => $this->form->name . '-' . $this->name			
		));
	}


	/**
	 * Set the control value
	 * @param   mixed   new value
	 * @return  bool
	 */
	public function setValue($value)
	{
		$this->value = $this->filter($value);
	}


	/**
	 * Return value
	 * @return  mixed
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * Adds rule for actual control ($this)
	 * @param   string       validation rule name or callback
	 * @param   mixed        validation argument
	 * @param   string       error message
	 * @return  Condition    return $this
	 */
	public function addRule($rule, $arg = null, $message = null)
	{
		if ($rule == Rule::FILLED || ($rule == Rule::LENGTH && $arg > 0))
			$this->htmlRequired = true;

		$this->rules[] = new Rule($this, $rule, $arg, $message);
		return $this;
	}


	/**
	 * Adds condition to input element
	 * @param   string|callback  rule name or callback
	 * @param   mixed            additional validation argument
	 * @return  FormCondition
	 */
	public function addCondition($rule, $arg = null)
	{
		return $this->conditions[] = new Condition($this, $rule, $arg);
	}


	/**
	 * Checks if control value is valid
	 * @return  bool
	 */
	public function isValid()
	{
		# chech conditions and add theirs rules
		foreach ($this->conditions as $condition) {
			if ($condition->isValid()) {
				foreach ($condition->rules as $rule)
					$this->rules[] = $rule;
			}
		}

		# validate rules
		foreach ($this->rules as $rule) {
			if (!$rule->isValid())
				return false;
		}

		return true;
	}


	/**
	 * Returns true when control was rendered
	 * @param   bool  set redendered as true
	 * @return  bool
	 */
	public function isRendered($set = false)
	{
		if ($set)
			$this->isRendered = true;

		return $this->isRendered;
	}

	public function getErrorLabel()
	{
		/** @var Html */
		$label = clone $this->htmlLabel;
		$label->setText($this->error);
		return $label;
	}

	/**
	 * Renders label tag
	 * @return  Html
	 */
	public function label()
	{
		return $this->getLabel();
	}


	/**
	 * Renders html control tag
	 * @return  Html
	 */
	public function control()
	{
		return $this->getControl();
	}

	public function error()
	{
		if ($this->hasError()) {
			$this->htmlErrorLabel->setText($this->error);
			$this->htmlError->setHtml($this->htmlErrorLabel);
		}
		
		return $this->htmlError;
	}

	/**
	 * Sets error
	 * @param   string    error text
	 * @return  FormControl
	 */
	public function setError($text)
	{
		$this->error = $text;
		return $this;
	}


	/**
	 * Checks whether control has errors
	 * @returns  bool
	 */
	public function hasError()
	{
		return !empty($this->error);
	}


	/**
	 * Returns html label
	 * @return  Html
	 */
	protected function getLabel()
	{
		return $this->htmlLabel;
	}


	/**
	 * Returns html control
	 * @return  Html
	 */
	protected function getControl()
	{
		$this->isRendered = true;
		return $this->htmlControl;
	}


	/**
	 * Returns form tag
	 * @return  Html
	 */
	public function getForm()
	{
		return $this->form;
	}


	/**
	 * Returns form name
	 * @param   bool     return full name with form
	 * @return  string
	 */
	public function getName($fullName = false)
	{
		if ($fullName)
			return $this->form->name . '-' . $this->name;

		return $this->name;
	}


	/**
	 * Returns if control is required
	 * @return  bool
	 */
	public function getHtmlRequired()
	{
		return $this->htmlRequired;
	}


	/**
	 * Returns empty value
	 * @return  mixed
	 */
	public function getEmptyValue()
	{
		return $this->emptyValue;
	}


	/**
	 * Returns rules
	 * @return  array
	 */
	public function getRules()
	{
		return $this->rules;
	}


	/**
	 * Returns conditions
	 * @reutrn  array
	 */
	public function getConditions()
	{
		return $this->conditions;
	}


	/**
	 * Sets empty value
	 * @param   mixed  empty value
	 * @return  FormControl  $this
	 */
	public function setEmptyValue($value)
	{
		$this->emptyValue = $value;
		return $this;
	}


	/**
	 * Magic method
	 */
	public function __get($key)
	{
		if (in_array($key, array('label', 'control', 'error')))
			return $this->{$key}();
		else
			return parent::__get($key);
	}


	/**
	 * Returns default value for html tag
	 * @return  string
	 */
	protected function getHtmlValue()
	{
		if ($this->value === '0' || !empty($this->value))
			return $this->value;
		else
			return $this->emptyValue;
	}



	/**
	 * Filters value by filters and null them if value is equall to emptyValue
	 * @return  void
	 */
	protected function filter($value)
	{
		foreach ($this->filters as $filter)
			$value = (string) call_user_func($filter, $value);

		if ($this->emptyValue == $value)
			$value = '';

		return $value;
	}


}


abstract class FormInputControl extends FormControl
{

	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->setTag('input');
	}

	protected function getControl()
	{
		return parent::getControl()->value($this->getHtmlValue());
	}

}