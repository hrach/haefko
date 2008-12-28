<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Application
 * @subpackage  Localization
 */


class L10n
{


	/** @var string */
	public static $domain = 'messages';

	/** @var string */
	public static $lang = 'cs';

	/** @var array Alowed langs */
	private static $langs = array();

	/** @var array */
	private static $map = array(
		'be' => 'be_BY', 'bg' => 'bg_BG', 'bs' => 'bs_BA',
		'ca' => 'ca_ES', 'cs' => 'cs_CZ', 'da' => 'da_DK',
		'et' => 'et_EE', 'eu' => 'eu_ES', 'gl' => 'gl_ES',
		'he' => 'he_IL', 'hi' => 'hi_IN', 'hr' => 'hr_HR',
		'hu' => 'hu_HU', 'hy' => 'hy_AM', 'is' => 'is_IS',
		'ja' => 'ja_JP', 'lt' => 'lt_LT', 'lv' => 'lv_LV',
		'mk' => 'mk_MK', 'mt' => 'mt_MT', 'nb' => 'nb_NO',
		'nn' => 'nn_NO', 'pl' => 'pl_PL', 'sk' => 'sk_SK',
		'sl' => 'sl_SI', 'sq' => 'sq_AL', 'th' => 'th_TH',
		'tn' => 'tn_ZA', 'tr' => 'tr_TR', 'ts' => 'ts_ZA',
		'uk' => 'uk_UA', 've' => 've_ZA', 'xh' => 'xh_ZA',
		'zu' => 'zu_ZA'
	);


	/**
	 * Inits default configuration
	 * @return  void
	 */
	public static function init()
	{
		self::domain(self::$domain);
	}


	/**
	 * Inits configurations from Config
	 * @return  void
	 */
	public static function initConfig()
	{
		self::$langs = Config::read('L10n.langs', array());
		self::$domain = Config::read('L10n.domain', self::$domain);

		switch (Config::read('L10n.autodetect', 'config')) {
			case 'browser': self::langByBrowser(); break;
			case 'url': self::langByUrl(Config::read('L10n.url.var', 'lang')); break;
			default: self::langByConfig();
		}

		self::domain(self::$domain);
	}


	/**
	 * Translates expression
	 * @param   string    key for tranlation
	 * @param   string    domain
	 * @return  string
	 */
	public static function __($string, $domain = null)
	{
		if (is_null($domain))
			$domain = self::$domain;

		return dgettext($domain, $string);
	}


	/**
	 * Translates expression in plural form
	 * @param   string    key for translation - one
	 * @param   string    key for translation - many
	 * @param   int       count
	 * @param   string    domain
	 * @param   bool      replace count?
	 * @return  string
	 */
	public static function __n($singular, $plural, $count, $domain = null, $replace = true)
	{
		if (is_null($domain))
			$domain = self::$domain;

		if ($replace)
			return sprintf(dngettext($domain, $singular, $plural, $count), $count, true);
		else
			return dngettext($domain, $singular, $plural, $count);
	}


	/**
	 * Adds domain
	 * @param   string    domain
	 * @param   string    enconding
	 * @param   bool      set as default?
	 */
	public static function domain($name, $encoding = 'utf-8', $activate = true)
	{
		$path = Application::get()->path . '/locales';
		bindtextdomain($name, $path);
		bind_textdomain_codeset($name, $encoding);

		if ($activate) {
			self::$domain = $name;
			textdomain($name);
		}
	}


	/**
	 * Sets language
	 * @param   string    language
	 * @return  bool
	 */
	public static function lang($lang)
	{
		if (isset(self::$map[$lang]))
			$lang = self::$map[$lang];

		putenv("LANG=$lang");
		setlocale(LC_ALL, $lang); 
		return true;
	}


	/**
	 * Detects lang by browser headers
	 * @return  bool
	 */
	public static function langByBrowser()
	{
		$langs = preg_split('#,\s?#', (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : '');
		foreach ($langs as $lang) {
			if (preg_match('#^(\w*(?:-\w*)?)(?:;q=.+)?$#U', $lang, $match)) {
				$lang = str_replace('-', '_', $match[1]);
				if (!self::isAllowed($lang))
					continue;

				return self::lang($lang);
			}
		}

		return false;
	}


	/**
	 * Detects lang by configuration
	 * @param   void
	 */
	public static function langByConfig()
	{
		self::lang(Config::read('L10n.lang', self::$lang));
	}


	/**
	 * Detects lang by url variable
	 * @param   string    routing variable name
	 * @return  bool
	 */
	public static function langByUrl($variable = 'lang')
	{
		if (isset(Router::$args[$variable])) {
			if (self::isAllowed($lang)) {
				self::lang(Router::$args[$variable]);
				return true;
			}
		}

		self::langByConfig();
		return false;
	}


	/**
	 * Is lang allowed?
	 * @param   string    language
	 * @return  bool
	 */
	public static function isAllowed($lang)
	{
		if (count(self::$langs) == 0)
			return true;

		return in_array($lang, self::$langs);
	}


}


/**
 * Wrapper for L10n::__()
 * @see L10n::__();
 */
function __($string, $domain = null)
{
	$args = func_get_args();
	return call_user_func_array(array('L10n', '__'), $args);
}


/**
 * Wrapper for L10n::__n()
 * @see L10n::__n();
 */
function __n($singular, $plural, $count, $domain = null)
{
	$args = func_get_args();
	return call_user_func_array(array('L10n', '__n'), $args);
}


L10n::init();