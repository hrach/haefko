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


require_once dirname(__FILE__) . '/Http.php';


/**
 * Trida pro praci se session
 */
class Session
{


	/** @var bool */
	private static $started = false;


	/**
	 * Star session
	 * @return  void
	 */
	public static function start()
	{
		self::checkHeaders();
		session_start();
		self::$started = true;
	}


	/**
	 * Read session variable
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
	 * Safe read.
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
		if (function_exists('ini_set')) {
			ini_set('session.use_cookies', 1);

			$name  = 'haefko-session';
			$lifeTime = 259200; // 3 days
			$path = Http::$baseUri;
			$domain = Http::$domain;

			if (class_exists('Config', false)) {
				if (Config::read('Session.temp') !== false)
					ini_set('session.save_path', Config::read('Session.temp'));

				$name = Config::read('Session.name', $name);
				$lifeTime = Config::read('Session.lifeTime', $lifeTime);
				$path = config::read('Session.path', $path);
				$domain = config::read('Session.domain', $domain);
			}

			if (substr_count($domain, ".") == 1)
				$domain = ".$domain";
			else
				$domain = preg_replace ('/^([^.])*/i', null, $domain);

			ini_set('session.name', $name);
			ini_set('session.cookie_lifetime', $lifeTime);
			ini_set('session.cookie_path', $path);
			ini_set('session.cookie_domain', $domain);
		}
	}


	private static function checkHeaders()
	{
		if (headers_sent())
			throw new Exception("Sessions nelze zapnout, hlavicky byly jiz odeslany.");
	}


}


Session::init();