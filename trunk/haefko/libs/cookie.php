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
 */


class Cookie
{


	/**
	 * Contructor
	 */
	private function __contruct()
	{}


	/**
	 * Gets cookie variable
	 * @param string $var var name
	 * @param mixed $default default value
	 * @return mixed
	 */
	public static function get($var, $default = null)
	{
		if (isset($_COOKIE[$var]))
			return $_COOKIE[$var];

		return $default;
	}


	/**
	 * Sets cooke variable
	 * @param string $var var name
	 * @param mixed $val
	 * @param string $path
	 * @param string $domain
	 * @param int $expires
	 */
	public static function set($var, $val, $path = null, $domain = null, $expires = null)
	{
		if ($expires === null) {
			$expires = self::$defaultExpires;
			if (class_exists('Config', false))
				$expires = Config::read('cookie.lite-time', $expires);

			$expires += time();
		}

		setcookie($var, $val, $expires, $path, $domain);
	}


	/**
	 * Check if variable exists
	 * @param string $var var name
	 * @return bool
	 */
	public static function exists($var)
	{
		return isset($_COOKIE[$var]);
	}


	/**
	 * Deletes cookie variable
	 * @param string $var var name
	 * @param string $path
	 * @param string $domain
	 */
	public static function delete($var, $path = null, $domain = null)
	{
		setcookie($var, false, time() - 60000, $path, $domain);
	}


}