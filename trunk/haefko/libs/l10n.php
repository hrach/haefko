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


class L10n
{

	/** @var string */
	public static $domain;

	/** @var string */
	public static $lang;

	/** @var string */
	public static $path;


	/**
	 * Private contructor
	 */
	private function __construct()
	{}


	/**
	 * Inits default configuration
	 */
	public static function init()
	{
		if (class_exists('Config', false))
			self::initConfig();

		self::domain(self::$domain);
		self::lang(self::$lang);
	}


	/**
	 * Inits configurations from Config
	 */
	public static function initConfig()
	{
		self::$domain = Config::read('l10n.domain');
		self::$lang = Config::read('l10n.lang');
		self::$path = Config::read('l10n.path');

		if (empty(self::$path) && class_exists('Application', false))
			self::$path = Application::get()->path . '/locales';
	}


	/**
	 * Translates expression
	 * @param string $string translation key
	 * @param string $domain
	 * @return string
	 */
	public static function __($string, $domain = null)
	{
		if ($domain === null)
			$domain = self::$domain;

		return dgettext($domain, $string);
	}


	/**
	 * Translates expression in plural form
	 * @param string $singular key for translation in singular form
	 * @param string $plural key for translation in plural form
	 * @param int $count
	 * @param string $domain
	 * @param bool $replace replace count in translated expression
	 * @return string
	 */
	public static function __n($singular, $plural, $count, $domain = null, $replace = true)
	{
		if ($domain === null)
			$domain = self::$domain;

		$tran = dngettext($domain, $singular, $plural, $count);
		if ($replace)
			return sprintf($tran, $count, true);
		else
			return $tran;
	}


	/**
	 * Binds translation domain
	 * @param string $name domain name
	 * @param string $enconding
	 * @param bool $activate set as default?
	 */
	public static function domain($name, $encoding = 'utf-8', $activate = true)
	{
		if (empty($name))
			$name = self::$lang;

		bindtextdomain($name, self::$path);
		bind_textdomain_codeset($name, $encoding);

		if ($activate) {
			self::$domain = $name;
			textdomain($name);
		}
	}


	/**
	 * Sets language
	 * @param string $lang lang name
	 * @return bool
	 */
	public static function lang($lang)
	{
		@putenv("LANG=$lang");
		setlocale(LC_ALL, $lang);
		return true;
	}


	/**
	 * Returns array of requested languages by browser header
	 * @return array|null
	 */
	public static function getBrowserLangs()
	{
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			return null;

		$langs = array();
		$alangs = preg_split('#,\s?#', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		foreach ($alangs as $lang) {
			if (preg_match('#^(.*)(?:;q=(.+))?$#U', $lang, $match)) {
				if (isset($match[2]))
					$langs[$match[2]] = $match[1];
				else
					$langs[1] = $match[1];
			}
		}

		krsort($langs);
		return $langs;
	}


}


/**
 * Wrapper for L10n::__()
 * @see L10n::__();
 */
function __($string, $domain = null)
{
	return L10n::__($string, $domain);
}


/**
 * Wrapper for L10n::__n()
 * @see L10n::__n();
 */
function __n($singular, $plural, $count, $domain = null, $replace = true)
{
	return L10n::__n($singular, $plural, $count, $domain, $replace);
}


L10n::init();