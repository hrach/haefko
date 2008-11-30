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


require_once dirname(__FILE__) . '/Http.php';


/**
 * Trida pro praci se session
 */
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
	 * Reads session variable
	 * @param   string  variable name
	 * @param   mixed   default value
	 * @return  mixed
	 */
	public static function read($var, $default = null)
	{
		if (!self::$started)
			self::start();

		if (isset($_SESSION[$var]))
			return $_SESSION[$var];
		else
			$default;
	}


	/**
	 * Safe read
	 * @param   string  variable name
	 * @param   mixed   default value
	 * @return  mixed
	 */
	public static function safeRead($var, $default = null)
	{
		if (isset($_COOKIE[ini_get('session.name')]))
			return self::read($var, $default);

		return $default;
	}


	public static function exists($var)
	{
		if (!self::$started)
			self::start();

		return isset($_SESSION[$var]);
	}


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


	public static function delete($var)
	{
		if (!self::$started)
			self::start();

		unset($_SESSION[$var]);
	}


	public static function destroy()
	{
		session_destroy();
	}


	public static function init()
	{
		self::$name = 'haefko-session';
		self::$lifeTime = 259200; // 3 days
		self::$path = Http::$baseUri;
		self::$domain = Http::$domain;
		self::$crossDomain = false;
		self::$secure = false;

		self::writeConfig();
	}


	public static function initConfig()
	{
		self::$name = Config::read('Session.name', self::$name);
		self::$lifeTime = Config::read('Session.lifeTime', self::$lifeTime);
		self::$path = Config::read('Session.path', self::$path);
		self::$domain = Config::read('Session.domain', self::$domain);
		self::$crossDomain = Config::read('Session.crossDomain', self::$crossDomain);
		self::$secure = Config::read('Session.secure', self::$secure);

		self::writeConfig();
	}


	private static function checkHeaders()
	{
		if (headers_sent($file, $line))
			throw new Exception("Headers has been already sent in $file on line $line.");
	}


	private static function writeConfig()
	{
		if (function_exists('ini_set'))
			ini_set('session.use_cookies', 1);

		$domain = self::$domain;
		if (self::$crossDomain) {
			if (substr_count($domain, ".") == 1)
				$domain = ".$domain";
			else
				$domain = preg_replace('/^([^.])*/i', null, $domain);
		}

		session_set_cookie_params(self::$lifeTime, self::$path, $domain, self::$secure);
	}


}


Session::init();