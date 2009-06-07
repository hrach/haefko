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
		$this->rules[] = $this->getRule($rule);
		return $this;
	}


	/**
	 * Adds condition
	 * @param   Condition $condition
	 * @return  JqueryFormJsValidator
	 */
	public function addCondition(Condition $condition)
	{
		$c = array(
			'control' => $condition->control->getName(),
			'rule' => $condition->rule,
			'negative' => $condition->negative,
			'default' => $condition->control->getDefaultValue(),
			'arg' => ($condition->arg instanceof FormControl) ? array('control' => $condition->arg->getName(true)) : $condition->arg,
		);

		foreach ($condition->rules as $rule)
			$c['rules'][] = $this->getRule($rule);

		$this->conditions[] = $c;
		return $this;
	}


	/**
	 * Returns raw js code
	 * @return  string
	 */
	public function getCode()
	{
		$name  = $this->name;
		$code  = "var {$name}Rules = " . json_encode($this->rules) . ";\n";
		$code .= "var {$name}Conditions = " . json_encode($this->conditions) . ";\n";
		$code .= "$('#{$name}').validate({$name}Rules, {$name}Conditions);\n";

		$this->rules = $this->conditions = array();
		return "<script type=\"text/javascript\">\n/* <![CDATA[ */\n$(document).ready(function(){\n" . $code . "});\n/* ]]> */\n</script>";
	}


	/**
	 * Transforms rule to array
	 * @param   Rule $rule
	 * @return  array
	 */
	protected function getRule(Rule $rule)
	{
		if (empty($this->name))
			$this->name = $rule->control->form->name;

		return array(
			'control' => $rule->control->getName(true),
			'rule' => $rule->rule,
			'negative' => $rule->negative,
			'default' => $rule->control->getDefaultValue(),
			'arg' => ($rule->arg instanceof FormControl) ? array('control' => $rule->arg->getName(true)) : $rule->arg,
			'message' => $rule->getMessage()
		);
	}


}