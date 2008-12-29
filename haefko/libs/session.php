<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Libs
 */


require_once dirname(__FILE__) . '/http.php';


class Session
{


	/** @var bool */
	private static $started = false;

	/** @var string */
	protected static $name;

	/** @var string */
	protected static $path;

	/** @var string */
	protected static $domain;

	/** @var int */
	protected static $lifeTime;

	/** @var bool */
	protected static $crossDomain;

	/** @var bool */
	protected static $secure;


	/**
	 * Starts session
	 * @return  void
	 */
	public static function start()
	{
		self::checkHeaders();
		session_start();
		self::$started = true;
	}


	/**
	 * Destroys session
	 * @return  void
	 */
	public static function destroy()
	{
		session_destroy();
	}


	/**
	 * Reads session variable
	 * @param   string    variable name
	 * @param   mixed     default value
	 * @return  mixed
	 */
	public static function read($var, $default = null)
	{
		if (!self::$started)
			self::start();

		if (array_key_exists($var, $_SESSION))
			return $_SESSION[$var];
		else
			return $default;
	}


	/**
	 * Safe reads session variable 
	 * If session is not started returns default
	 * @param   string    variable name
	 * @param   mixed     default value
	 * @return  mixed
	 */
	public static function safeRead($var, $default = null)
	{
		if (isset($_COOKIE[self::$name]))
			return self::read($var, $default);

		return $default;
	}


	/**
	 * Checks if session $var exists
	 * @param   string    variable name
	 * @return  bool
	 */
	public static function exists($var)
	{
		if (!self::$started)
			self::start();

		return array_key_exists($var, $_SESSION);
	}


	/**
	 * Writes $val to session $var
	 * @param   string    variable name
	 * @param   mixed     value
	 * @return  void
	 */
	public static function write($var, $val)
	{
		if (!self::$started)
			self::start();

		if (is_array($var)) {
			foreach ($var as $key => $val)
				$_SESSION[$key] = $val;
		} else {
			$_SESSION[$var] = $val;
		}
	}


	/**
	 * Unsets session variable
	 * @param   string    variable name
	 * @return  void
	 */
	public static function delete($var)
	{
		if (!self::$started)
			self::start();

		unset($_SESSION[$var]);
	}


	/**
	 * Inits default configuration
	 * @return  void
	 */
	public static function init()
	{
		self::$name = 'haefko-session';
		self::$lifeTime = 259200; // 3 days
		self::$path = Http::$baseUri;
		self::$domain = Http::$domain;
		self::$crossDomain = false;
		self::$secure = false;

		if (class_exists('Config', false))
			self::initConfig();

		self::writeConfig();
	}


	/**
	 * Inits configurations from Config
	 * @return  void
	 */
	public static function initConfig()
	{
		self::$name = Config::read('Session.name', self::$name);
		self::$lifeTime = Config::read('Session.lifeTime', self::$lifeTime);
		self::$path = Config::read('Session.path', self::$path);
		self::$domain = Config::read('Session.domain', self::$domain);
		self::$crossDomain = Config::read('Session.crossDomain', self::$crossDomain);
		self::$secure = Config::read('Session.secure', self::$secure);
	}


	/**
	 * Checks sent headers
	 * @throws  Exception
	 * @return  void
	 */
	private static function checkHeaders()
	{
		$line = $file = null;
		if (headers_sent($file, $line))
			throw new Exception("Headers has been already sent in $file on line $line.");
	}


	/**
	 * Sets session configuration
	 * @return  void
	 */
	private static function writeConfig()
	{
		if (function_exists('ini_set'))
			ini_set('session.use_cookies', 1);


		$domain = self::$domain;
		if (substr_count($domain, ".") == 1 && self::$crossDomain)
			$domain = ".$domain";
		else
			$domain = preg_replace('#^([^.])*#i', null, $domain);

		session_name(self::$name);
		session_set_cookie_params(self::$lifeTime, self::$path, $domain, self::$secure);
	}


}


Session::init();