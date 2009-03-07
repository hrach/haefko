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


require_once dirname(__FILE__) . '/controls/texts.php';
require_once dirname(__FILE__) . '/controls/checkboxes.php';
require_once dirname(__FILE__) . '/controls/selects.php';
require_once dirname(__FILE__) . '/controls/radio.php';
require_once dirname(__FILE__) . '/controls/file.php';
require_once dirname(__FILE__) . '/controls/buttons.php';


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

	/** @var array */
	protected $errors = array();

	/** @var array */
	protected $rules = array();

	/** @var array */
	protected $conditions = array();

	/** @var string */
	protected $htmlId;

	/** @var string */
	protected $htmlTag;

	/** @var array */
	protected $htmlRequired = false;

	/** @var bool */
	protected $htmlRendered = false;


	/**
	 * Constructor
	 * @param   Form     form
	 * @param   string   control name
	 * @param   mixed    label (null = from name, false = no label)
	 * @return  void
	 */
	public function __construct(Form $form, $name, $label = null)
	{
		$this->name = $name;
		$this->form = $form;
		$this->htmlId = "{$form->name}-$name";

		$this->control = Html::el($this->htmlTag);
		$this->control->name = "{$this->form->name}[$name]";

		if ($label instanceof Html)
			$this->label = $label;
		elseif ($label !== false)
			$this->label = Html::el('label', is_null($label) ? ucfirst($name) : $label);
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
		return $this->addRuleOn($this, $rule, $arg, $message);
	}


	/**
	 * Adds rule for $control
	 * @param   FormControl  control
	 * @param   string       validation rule name or callback
	 * @param   mixed        validation argument
	 * @param   string       error message
	 * @return  FormControl  return $this
	 */
	public function addRuleOn(FormControl $control, $rule, $arg = null, $message = null)
	{
		if ($rule == Form::FILLED || ($rule == Form::LENGTH && $arg > 0))
			$this->htmlRequired = true;

		$r = new Rule();
		$r->control = $control;
		$r->rule = $rule;
		$r->arg = $arg;
		$r->message = $message;

		$this->rules[] = $r;
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
		$r = new Rule();
		$r->rule = $rule;
		$r->control = $this;
		$r->arg = $arg;

		$this->conditions[] = new Condition($r);
		return end($this->conditions);
	}


	/**
	 * Checks if control value is valid
	 * @return  bool
	 */
	public function isValid()
	{
		# chech conditions and add theirs rules
		foreach ($this->conditions as $condition) {
			$rule = $condition->rule;
			if (Rule::isValid($rule->rule, $rule->control->getValue(), $rule->arg)) {
				foreach ($condition->rules as $rule)
					$this->rules[] = $rule;
			}
		}

		# validate rules
		$valid = true;
		foreach ($this->rules as $rule) {
			if (!Rule::isValid($rule->rule, $rule->control->getValue(), $rule->arg)) {
				$rule->control->errors[] = $this->message($rule->rule, $rule->message, $rule->arg);
				$valid = false;
			}
		}

		return $valid;
	}


	/**
	 * Returns true when control was rendered
	 * @param   bool  set redendered as true
	 * @return  bool
	 */
	public function isRendered($set = false)
	{
		if ($set)
			$this->htmlRendered = true;

		return $this->htmlRendered;
	}


	/**
	 * Renders label tag
	 * @param   array   attributes
	 * @return  string
	 */
	public function label($attrs = array())
	{
		if ($this->label === false)
			return null;

		$this->prepareLabel();
		$this->label->setAttrs($attrs);
		return $this->label->render();
	}


	/**
	 * Renders html control tag
	 * @param   array   attributes
	 * @return  string
	 */
	public function control($attrs = array())
	{
		$this->prepareControl();
		$this->htmlRendered = true;
		$this->control->setAttrs($attrs);
		return $this->control->render();
	}


	/**
	 * Adds error
	 * @param   string    error text
	 * @return  void
	 */
	public function addError($text)
	{
		$this->errors[] = $text;
	}


	/**
	 * Renders html error label(s)
	 * @return  string
	 */
	public function errors()
	{
		$errors = Html::el('div', null, array('id' => "{$this->htmlId}-errors", 'class' => 'input-errors'));
		foreach ($this->errors as $error)
			$errors->setHtml("<label class=\"error\" for=\"{$this->htmlId}\">$error</label>\n");

		return $errors->render();
	}


	/**
	 * Checks whether control has errors
	 * @returns  bool
	 */
	public function hasErrors()
	{
		return !empty($this->errors);
	}


	/**
	 * Returns html label
	 * @return  Html
	 */
	public function getLabel()
	{
		return $this->label;
	}


	/**
	 * Returns html control
	 * @return  Html
	 */
	public function getControl()
	{
		return $this->control;
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
	 * @return  string
	 */
	public function getName()
	{
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
		if (in_array($key, array('label', 'control', 'errors')))
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
		if (empty($this->value) && $this->value !== '0')
			return $this->emptyValue;
		else
			return $this->value;
	}


	/**
	 * Prepares label for rendering
	 * @return  void
	 */
	protected function prepareLabel()
	{
		if ($this->htmlRequired)
			$this->label->class = 'required';

		$this->label->for = $this->htmlId;
		$this->label->id = "{$this->htmlId}-label";
	}


	/**
	 * Prepares control for rendering
	 * @return  void
	 */
	protected function prepareControl()
	{
		$this->control->id = $this->htmlId;
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


	/**
	 * Prepares message
	 * @param   Rule    rule
	 * @param   string  message
	 * @param   mixed   arguments
	 * @return  string
	 */
	protected function message($rule, $message, $arg)
	{
		if (empty($message))
			$message = Rule::$messages[$rule];

		if (is_array($arg)) {
			array_unshift($arg, $message);
			$message = call_user_func_array('sprintf', $arg);
		} elseif (!is_object($arg)) {
			$message = sprintf($message, $arg);
		}

		if (empty($message))
			$message = 'Undefined error message.';

		return $message;
	}


}


abstract class FormInputControl extends FormControl
{

	protected $htmlTag = 'input';

	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->value = $this->getHtmlValue();
		$this->control->type = $this->htmlType;
		$this->control->class = $this->htmlTypeClass;
	}

}