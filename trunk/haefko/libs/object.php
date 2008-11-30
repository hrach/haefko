<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko_Libs
 */


abstract class Object
{


	/**
	 * Returns class name
	 * @return  string
	 */
	public function getClass()
	{
		return get_class($this);
	}


	/**
	 * Magic method
	 * @throws  Exception
	 * @return  mixed
	 */
	public function __get($key)
	{
		if (method_exists($this, "get$key"))
			return $this->{"get$key"}();
		else
			throw new Exception("Undefined variable " . $this->getClass() . "::$$key.");
	}


	/**
	 * Magic method
	 * @throws  Exception
	 * @return  mixed
	 */
	public function __set($key, $value)
	{
		if (method_exists($this, "set$key")) {
			return $this->{"set$key"}($value);
		} else {
			if (method_exists($this, "get$key"))
				throw new Exception("Variable " . $this->getClass() . "::$$key is read-only.");
			else
				throw new Exception("Undefined variable " . $this->getClass() . "::$$key.");
		}
	}


}