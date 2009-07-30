<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
 * @subpackage  Forms
 */


class Condition extends Rule
{

	/** @var array */
	public $rules = array();


	/**
	 * Returns true when is the rule valid
	 * @return bool
	 */
	public function isValid()
	{
		$valid = $this->validate($this->rule, $this->control->getValue(), $this->arg);
		if ($this->negative)
			$valid = !$valid;

		return $valid;
	}


	/**
	 * Add rule for actual control (control of condition)
	 * @param string $rule validation rule name or callback
	 * @param mixed $arg validation argument
	 * @param string $message error message
	 * @return Condition
	 */
	public function addRule($rule, $arg = null, $message = null)
	{
		return $this->addRuleOn($this->control, $rule, $arg, $message);
	}


	/**
	 * Add rule for $control
	 * @param FormControl $control
	 * @param string $rule validation rule name or callback
	 * @param mixed $arg validation argument
	 * @param string $message error message
	 * @return Condition
	 */
	public function addRuleOn(FormControl $control, $rule, $arg = null, $message = null)
	{
		$this->rules[] = new Rule($control, $rule, $arg, $message);
		return $this;
	}


}