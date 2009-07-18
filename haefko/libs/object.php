<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko_Libs
 */


abstract class Object
{


	/**
	 * Returns class name
	 * @return string
	 */
	public function getClass()
	{
		return get_class($this);
	}


	/**
	 * Returns class ancestors
	 * @return array
	 */
	public function getAncestors()
	{
		$class = $this;
		$classes = array($this);
		while($class = get_parent_class($class))
			$classes[] = $class;

		return $classes;
	}


	/**
	 * Magic method
	 * @throws Exception
	 * @return mixed
	 */
	public function __get($key)
	{
		if (substr($key, 0, 2) == 'is' && method_exists($this, $key))
			return $this->{$key}();
		elseif (method_exists($this, "get$key"))
			return $this->{"get$key"}();
		else
			throw new Exception("Undefined variable " . $this->getClass() . "::$$key.");
	}


	/**
	 * Magic method
	 * @throws Exception
	 * @return mixed
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


	/**
	 * Interface __call()
	 * @param mixed $method method name
	 * @param mixed $args
	 * @throws Exception
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		if (empty($method))
			throw new Exception("Method name can not be empty.");

		$classes = $this->getAncestors($this);
		foreach ($classes as $class) {
			$class = get_class($class);
			if (function_exists("prototype_{$class}_$method")) {
				array_unshift($args, $this);
				return call_user_func_array("prototype_{$class}_$method", $args);
			}
		}

		throw new Exception('Undefined method ' . get_class($this) . "::$method().");
	}


}