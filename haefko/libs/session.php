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

	/** @var array */
	protected static $namespaces = array();


	/**
	 * Returns namepsace session object
	 * @param $namespace namespace name
	 * @return SessionNamespace
	 */
	public static function getNamespace($namespace)
	{
		if (!self::$started) self::start();

		$namespace = strtolower($namespace);
		require_once dirname(__FILE__) . '/session-namespace.php';
		if (isset(self::$namespaces[$namespace]))
			return self::$namespaces[$namespace];
		else
			return self::$namespaces[$namespace] = new SessionNamespace($namespace);
	}


	/**
	 * Starts session
	 * @return void
	 */
	public static function start()
	{
		self::checkHeaders();
		session_start();
		self::$started = true;
	}


	/**
	 * Destroys session
	 * @return void
	 */
	public static function destroy()
	{
		session_destroy();
	}


	/**
	 * Returns session id / name
	 * @return string
	 */
	public static function getName()
	{
		if (!self::$started) self::start();
		return session_id();
	}


	/**
	 * Reads session variable
	 * @param string $var variable name
	 * @param mixed $default default value
	 * @return mixed
	 */
	public static function read($var, $default = null)
	{
		if (!self::$started) self::start();
		if (array_key_exists($var, $_SESSION))
			return $_SESSION[$var];
		else
			return $default;
	}


	/**
	 * Safe reads session variable
	 * If session is not started returns default
	 * @param string $var variable name
	 * @param mixed $deafult default value
	 * @return mixed
	 */
	public static function safeRead($var, $default = null)
	{
		if (isset($_COOKIE[self::$name]))
			return self::read($var, $default);

		return $default;
	}


	/**
	 * Returns true if session variable exists
	 * @param string $var variable name
	 * @return bool
	 */
	public static function exists($var)
	{
		if (!self::$started) self::start();
		return array_key_exists($var, $_SESSION);
	}


	/**
	 * Writes $val to session $var
	 * @param string|array $var variable name
	 * @param mixed $val value
	 * @return void
	 */
	public static function write($var, $val)
	{
		if (!self::$started) self::start();
		if (is_array($var)) {
			foreach ($var as $key => $val)
				$_SESSION[$key] = $val;
		} else {
			$_SESSION[$var] = $val;
		}
	}


	/**
	 * Deletes session variable
	 * @param string $var variable name
	 * @return void
	 */
	public static function delete($var)
	{
		if (!self::$started) self::start();
		unset($_SESSION[$var]);
	}


	/**
	 * Inits default configuration
	 * @return void
	 */
	public static function init()
	{
		self::$name = 'haefko-session';
		self::$lifeTime = 259200; # 3 days
		self::$path = Http::$baseURL . '/';
		self::$domain = Http::$domain;
		self::$crossDomain = false;
		self::$secure = false;

		if (class_exists('Config', false))
			self::initConfig();

		self::writeConfig();
	}


	/**
	 * Inits configurations from Config
	 * @return void
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
	 * @throws Exception
	 * @return void
	 */
	private static function checkHeaders()
	{
		$line = $file = null;
		if (headers_sent($file, $line))
			throw new Exception("Headers has been already sent in $file on line $line.");
	}


	/**
	 * Sets session configuration
	 * @return void
	 */
	private static function writeConfig()
	{
		if (function_exists('ini_set'))
			ini_set('session.use_cookies', 1);

		$domain = self::$domain;
		if (self::$crossDomain) {
			if (substr_count($domain, '.') == 1)
				$domain = ".$domain";
			else
				$domain = preg_replace('#^([^.])*#i', '', $domain);
		} else {
			$domain = '';
		}

		session_name(self::$name);
		session_set_cookie_params(self::$lifeTime, rtrim(self::$path, '/') . '/', $domain, self::$secure);
	}


}


Session::init();