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


class Condition
{


	/** @var Rule */
	public $rule;

	/** @var array */
	public $rules = array();


	/**
	 * Constructor
	 * @param   Rule   rule
	 * @return  void
	 */
	public function __construct(Rule $rule)
	{
		$this->rule = $rule;
	}


	/**
	 * Add rule for actual control (control of condition)
	 * @param   string       validation rule name or callback
	 * @param   mixed        validation argument
	 * @param   string       error message
	 * @return  Condition    return $this
	 */
	public function addRule($rule, $arg = null, $message = null)
	{
		return $this->addRuleOn($this->rule->control, $rule, $arg, $message);
	}


	/**
	 * Add rule for $control
	 * @param   FormControl  control
	 * @param   string       validation rule name or callback
	 * @param   mixed        validation argument
	 * @param   string       error message
	 * @return  Condition    return $this
	 */
	public function addRuleOn(FormControl $control, $rule, $arg = null, $message = null)
	{
		$r = new Rule();
		$r->control = $control;
		$r->rule = $rule;
		$r->arg = $arg;
		$r->message = $message;

		$this->rules[] = $r;
		return $this;
	}


}