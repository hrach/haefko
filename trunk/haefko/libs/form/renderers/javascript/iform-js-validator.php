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


interface IFormJsValidator
{


	/**
	 * Adds rule
	 * @param   Rule  $rule
	 * @return  IFormJsValidator
	 */
	public function addRule(Rule $rule);


	/**
	 * Adds condition
	 * @param   Condition  $condition
	 * @return  IFormJsValidator
	 */
	public function addCondition(Condition $condition);


	/**
	 * Returns raw javascript code
	 * @return  string
	 */
	public function getCode();


}