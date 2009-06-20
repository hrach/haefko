<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Libs
 */


require_once dirname(__FILE__) . '/iform-js-validator.php';


class FormJqueryJsValidator extends Object implements IFormJsValidator
{


	/** @var string - Form name */
	protected $name;

	/** @var array */
	protected $rules;

	/** @var array */
	protected $conditions;


	/**
	 * Adds rule
	 * @param   Rule $rule
	 * @return  JqueryFormJsValidator
	 */
	public function addRule(Rule $rule)
	{
		$this->rules[] = $this->getRule($rule, true);
		return $this;
	}


	/**
	 * Adds condition
	 * @param   Condition $condition
	 * @return  JqueryFormJsValidator
	 */
	public function addCondition(Condition $condition)
	{
		$c = $this->getRule($condition);
		foreach ($condition->rules as $rule)
			$c['rules'][] = $this->getRule($rule, true);

		$this->conditions[] = $c;
		return $this;
	}


	/**
	 * Returns raw js code
	 * @return  string
	 */
	public function getCode()
	{
		if (empty($this->rules) && empty($this->conditions))
			return '';

		$name  = $this->name;
		$code  = "var {$name}Rules = " . json_encode($this->rules) . ";\n";
		$code .= "var {$name}Conditions = " . json_encode($this->conditions) . ";\n";
		$code .= "$('#{$name}').validate({$name}Rules, {$name}Conditions);\n";

		$this->rules = $this->conditions = array();
		return "<script type=\"text/javascript\">\n/* <![CDATA[ */\n$(document).ready(function(){\n" . $code . "});\n/* ]]> */\n</script>";
	}


	/**
	 * Transforms rule to array
	 * @param   Rule  $rule
	 * @param   bool  add message?
	 * @return  array
	 */
	protected function getRule(Rule $rule, $withMessage = false)
	{
		if (empty($this->name))
			$this->name = $rule->control->form->name;

		$r = array();
		$r['control'] = $rule->control->getName();
		$r['rule'] = $rule->rule;

		if ($rule->negative)
			$r['negative'] = $rule->negative;

		$default = $rule->control->getDefaultValue();
		if (!empty($default))
			$r['default'] = $default;

		if (!empty($rule->arg))
			$r['arg'] = ($rule->arg instanceof FormControl) ? array('control' => $rule->arg->getName()) : $rule->arg;

		if ($withMessage)
			$r['message'] = $rule->getMessage();

		return $r;
	}


}