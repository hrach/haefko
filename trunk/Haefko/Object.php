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


abstract class Object
{


	/**
	 * Retun class name
	 * @return  string
	 */
	public function getClass()
	{
		return get_class($this);
	}


	/**
	 * Magic method
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