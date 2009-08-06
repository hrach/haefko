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
 * @subpackage  Database
 */


class DbResultNode implements ArrayAccess, IteratorAggregate
{


	/**
	 * Constructor
	 * @param array $data
	 * @return DbResultNode
	 */
	public function __construct($data)
	{
		foreach ((array) $data as $key => $val)
			$this->$key = $val;
	}


	/**
	 * Magic method
	 * @throws Exception
	 */
	public function __get($name)
	{
		throw new Exception("Undefined field '$name'.");
	}


	/**
	 * Array-access interface
	 */
	public function offsetSet($key, $value)
	{
		$this->$key = $value;
	}


	/**
	 * Array-access interface
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		if (!property_exists($this, $key))
			throw new Exception("Undefined key '$key'.");

		return $this->$key;
	}


	/**
	 * Array-access interface
	 */
	public function offsetUnset($key)
	{
		unset($this->$key);
	}


	/**
	 * Array-access interface
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return isset($this->$key);
	}


	/**
	 * IteratorAggregate interface
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator((array) get_object_vars($this));
	}


}