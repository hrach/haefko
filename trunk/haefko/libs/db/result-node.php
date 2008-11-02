<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko
 */


/**
 * Interface node for db result
 * @subpackage  Database
 */
class DbResultNode implements ArrayAccess
{


	/**
	 * Constructor
	 * @param   array   data
	 * @return  void
	 */
	public function __construct($data)
	{
		foreach ((array) $data as $key => $val)
			$this->$key = $val;
	}


	/**
	 * Magic method
	 * @return  void
	 */
	public function __get($name)
	{
		throw new Exception("Undefined field '$name'.");
	}


	/**
	 * Array-access interface
	 * @return  void
	 */
	public function offsetSet($key, $value)
	{
		$this->$key = $value;
	}


	/**
	 * Array-access interface
	 * @return  FormItem
	 */
	public function offsetGet($key)
	{
		if (!property_exists($this, $key))
			throw new Exception("Undefined key '$key'.");

		return $this->$key;
	}


	/**
	 * Array-access interface
	 * @return  void
	 */
	public function offsetUnset($key)
	{
		throw new Exception("You can not unset the '$key'.");
	}


	/**
	 * Array-access interface
	 * @return  void
	 */
	public function offsetExists($key)
	{
		return isset($this->$key);
	}


}