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



class Cookie
{



	public static function read($var)
	{
		if (isset($_COOKIE[$var]))
			return $_COOKIE[$var];

		return false;
	}



	public static function exists($var)
	{
		return isset($_COOKIE[$var]);
	}



	public static function write($var, $val, $path = null, $domain = null, $expires = null)
	{
		self::checkHeaders();

		if (is_null($expires)) {
			$expires = 259200; // 3 days

			if (class_exists('Config', false))
				$expires = Config::read('Cookie.lifeTime', $expires);

			$expires += time();
		}

		setcookie($var, $val, $expires, $path, $domain);
	}



	public static function delete($var, $path = null, $domain = null)
	{
		self::checkHeaders();
		setcookie($var, false, time() - 60000, $path, $domain);
	}



	private static function checkHeaders()
	{
		if (headers_sent())
			throw new Exception("Nelze nastavit cookie, hlavicky byly jiz odeslany.");
	}



}