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


class Condition extends Rule
{


	/** @var array */
	public $rules = array();


	/**
	 * Add rule for actual control (control of condition)
	 * @param   string       validation rule name or callback
	 * @param   mixed        validation argument
	 * @param   string       error message
	 * @return  Condition    return $this
	 */
	public function addRule($rule, $arg = null, $message = null)
	{
		return $this->addRuleOn($this->control, $rule, $arg, $message);
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
		$this->rules[] = new Rule($control, $rule, $arg, $message);
		return $this;
	}


}