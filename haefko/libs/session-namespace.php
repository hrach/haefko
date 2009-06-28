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


class SessionNamespace extends Object
{

	/** @var string */
	protected $namespace;


	/**
	 * Constructor
	 * @param string namepsace name
	 * @return SessionNamespace
	 */
	public function __construct($namespace)
	{
		$this->namespace = $namespace;
		$this->deleteExpired();
	}


	/**
	 * Clears and deletes variables which are expired
	 * @return SessionNamespace
	 */
	protected function deleteExpired()
	{
		if (!isset($_SESSION['__M'][$this->namespace]))
			return;

		# expired namespace
		$namespace = $_SESSION['__M'][$this->namespace];
		if (isset($namespace['__E'])) {
			if ($namespace['__E'] <= time()) {
				unset($_SESSION[$this->namespace]['__D']);
				unset($_SESSION[$this->namespace]['__M']);
			}
		}

		if (!isset($namespace['__V']))
			return;

		# expired variables
		foreach ($namespace['__V'] as $var => $time) {
			if ($time <= time()) {
				unset($_SESSION['__D'][$this->namespace][$var]);
				unset($_SESSION['__M'][$this->namespace]['__V'][$var]);
			}
		}

		if (empty($_SESSION['__M'][$this->namespace]['__V']))
			unset($_SESSION['__M'][$this->namespace]);
	}


	/**
	 * Reads variable
	 * @param string variable name
	 * @param mixed defautl value (when variable is not set)
	 * @return mixed
	 */
	public function read($name, $default = null)
	{
		if (isset($_SESSION['__D'][$this->namespace][$name]))
			return $_SESSION['__D'][$this->namespace][$name];
		else
			return $default;
	}


	/**
	 * Writes variable value
	 * @param string variable name
	 * @param mixed variable value
	 * @param int|string expiration expression, null = no expiration
	 * @return SessionNamespace
	 */
	public function write($name, $value, $expiration = null)
	{
		if (is_string($expiration))
			$expiration = strtotime($expiration);

		$_SESSION['__D'][$this->namespace][$name] = $value;
		if ($expiration != null)
			$_SESSION['__M'][$this->namespace]['__V'][$name] = $expiration;

		return $this;
	}


	/**
	 * Checks if variable exists
	 * @param string variable name
	 * @return bool
	 */
	public function exists($name)
	{
		return isset($_SESSION['__D'][$this->namespace][$name]);
	}


	/**
	 * Deletes variable
	 * @param string variable name
	 * @return SessionNamespace
	 */
	public function delete($name)
	{
		unset($_SESSION['__D'][$this->namespace][$name]);
		unset($_SESSION['__M'][$this->namespace]['__V'][$name]);
		return $this;
	}


	/**
	 * Sets namespace expiration time
	 * @param int|string time expression
	 * @return SessionNamespace
	 */
	public function setExpiration($time)
	{
		if (is_string($time))
			$time = strtotime($time);

		$_SESSION['__M'][$this->namespace]['__E'] = $time;
		return $this;
	}


	/**
	 * Setted
	 * @param string key name
	 * @param mixed value
	 * @return void
	 */
	public function __set($key, $val)
	{
		$this->write($key, $val);
	}


	/**
	 * Getter
	 * @param string key name
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->read($key);
	}


}