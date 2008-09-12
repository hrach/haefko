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


abstract class FormControl
{


	/** @var mixed */
	public $emptyValue;

	/** @var array */
	public $filters = array();

	/** @var array */
	public $errors = array();


	/** @var string */
	protected $name;

	/** @var Form */
	protected $form;

	/** @var mixed */
	protected $value;

	/** @var array */
	protected $rules = array();

	/** @var array */
	protected $conditions = array();


	/** @var Html */
	protected $control;

	/** @var Html|bool */
	protected $label = false;

	/** @var string */
	protected $tag;

	/** @var inf */
	protected $counter = 0;

	/** @var array */
	protected $classes = array();


	/**
	 * Constructor
	 * @param   Form         form
	 * @param   string       control name
	 * @param   mixed        label (null = from name, false = no label)
	 * @return  void
	 */
	public function __construct(Form $form, $name, $label = null)
	{
		$this->name = $name;
		$this->form = $form;

		$this->control = Html::el($this->tag);
		$this->control->name = "{$this->form->name}[$name]";
		$this->id = "{$this->form->name}-$name";

		if ($label !== false)
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
	 * Add rule for actual control ($this)
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
	 * Add rule for $control
	 * @param   FormControl  control
	 * @param   string       validation rule name or callback
	 * @param   mixed        validation argument
	 * @param   string       error message
	 * @return  FormControl  return $this
	 */
	public function addRuleOn(FormControl $control, $rule, $arg = null, $message = null)
	{
		if ($rule == Form::FILLED)
			$this->classes[] = 'required';

		$r = new Rule();
		$r->control = $control;
		$r->rule = $rule;
		$r->arg = $arg;
		$r->message = $message;

		$this->rules[] = $r;
		return $this;
	}


	/**
	 * Add condition to input element
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
	 * Check if control value is valid
	 * @return  bool
	 */
	public function isValid()
	{
		foreach ($this->conditions as $condition) {
			$rule = $condition->rule;
			if (Rule::isValid($rule->rule, $rule->control->getValue(), $rule->arg)) {
				foreach ($condition->rules as $rule)
					$this->rules[] = $rule;
			}
		}

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
	 * Render control block - label & control tag + error label
	 * @param   array   attributes
	 * @return  string
	 */
	public function block($attrs = array())
	{
		$div = Html::el('div');
		$div->setAttributes($attrs);
		$div->class = array_merge((array) $div->class, $this->classes);

		if ($this->label === false)
			$div->setContent($this->control() . $this->error(), false);
		else
			$div->setContent($this->label() . $this->control() . $this->error(), false);

		$this->counter();
		return $div->render();
	}


	/**
	 * Render label tag
	 * @param   array   attributes
	 * @return  string
	 */
	public function label($attrs = array())
	{
		$this->prepareLabel();
		$this->label->setAttributes($attrs);
		return $this->label->render();
	}


	/**
	 * Render html control tag
	 * @param   array   attributes
	 * @return  string
	 */
	public function control($attrs = array())
	{
		$this->prepareControl();
		$this->control->setAttributes($attrs);
		return $this->control->render();
	}


	/**
	 * Render html error label(s)
	 * @param   bool          return as array?
	 * @return  string|array
	 */
	public function error($asArray = false)
	{
		$errors = array();
		foreach ($this->errors as $error)
			$errors[] = "<label class=\"error\" for=\"{$this->id}" . $this->counter(false) . "\">$error</label>";

		if ($asArray)
			return $errors;
		else
			return "<div class=\"errors\">" . implode("\n", $errors) . "</div>";
	}


	/**
	 * Return number of current control (and optionaly increase them)
	 * @param   bool  increment counter?
	 * @return  int
	 */
	public function counter($increment = true)
	{
		$c = '';
		if ($this->counter > 0)
			$c = $this->counter;

		if ($increment)
			++$this->counter;

		return $c;
	}


	/**
	 * Magic method
	 */
	public function __get($name)
	{
		if (in_array($name, array('label', 'control', 'block', 'error')))
			return $this->{$name}();
		else
			throw new Exception("Unexisting var FormControl::$$name.");
	}


	/**
	 * Return default value for control
	 * @return  string
	 */
	protected function getControlValue()
	{
		if (empty($this->value) && $this->value !== '0')
			return $this->emptyValue;
		else
			return $this->value;
	}


	/**
	 * Prepare label for rendering
	 * @return  void
	 */
	protected function prepareLabel()
	{
		$c = $this->counter(false);
		$this->label->for = $this->id . $c;
		$this->label->id = "{$this->id}-label$c";
	}


	/**
	 * Prepare control for rendering
	 * @return  void
	 */
	protected function prepareControl()
	{
		$this->control->id = $this->id . $this->counter(false);
	}


	/**
	 * Filter value by filters and null them if value is equall to emptyValue
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
	 * Prepare message
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
			$message = "!!! No error message !!!";

		return $message;
	}


}