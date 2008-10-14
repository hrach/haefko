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



/**
 * Lokalizacni trida
 */
class L10n
{

	/** @var string Domena */
	public static $domain = 'messages';

	/** @var string Jazyk */
	public static $lang = 'cs';

	/** @var array Pole dostupnych jazyku */
	private static $langs = array();

	/** @var array Tabulka pro prevod */
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
	 * Nastavi vychozi hodnoty
	 * @return
	 */
	public static function initialize()
	{
		self::$langs = Config::read('L10n.langs', array());

		switch (Config::read('L10n.autodetect', 'config')) {
		case 'browser':
			self::langByBrowser();
			break;
		case 'url':
			self::langByUrl(Config::read('L10n.url.var', 'lang'));
			break;
		default:
			self::langByConfig();
		}

		self::domain(self::$domain);
	}



	/**
	 * Prelozi vyraz
	 * @param   string  klic k prelozeni
	 * @param   string  domena
	 * @return  string
	 */
	public static function __($string, $domain = null)
	{
		if (is_null($domain))
			$domain = self::$domain;

		return dgettext($domain, $string);
	}



	/**
	 * Prelozi vyraz s ohledem na mnozne cisla
	 * @param   string  klic k prelozeni - en singular
	 * @param   string  klic k prelozeni - en plural
	 * @param   int     cislo
	 * @param   string  domena
	 * @return  string
	 */
	public static function __n($singular, $plural, $count, $domain = null)
	{
		if (is_null($domain))
			$domain = self::$domain;

		return sprintf(dngettext($domain, $singular, $plural, $count), $count, true);
	}



	/**
	 * Prida domenu
	 * @param   string  jmeno domeny
	 * @param   string  kodovani
	 * @param   bool    aktivavat domenu jako vyhozi
	 */
	public static function domain($name, $encoding = 'utf-8', $activate = true)
	{
		$path = Application::i()->path . 'locales';
		bindtextdomain($name, $path);
		bind_textdomain_codeset($name, $encoding);

		if ($activate) {
			self::$domain = $name;
			textdomain($name);
		}
	}



	/**
	 * Nastavi jazyk
	 * @param   string  jazyk
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
	 * Detekuje jazyk podle prohlizecem zaslane hlavicky
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
	 * Nastavi jazyk podle konfigurace
	 * @param   void
	 */
	public static function langByConfig()
	{
		self::lang(Config::read('L10n.lang', self::$lang));
	}



	/**
	 * Detekuje a nastavi jazyk podle url
	 * @param   string  jmeno promenne s nazvem jazyka
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
	 * Zjisti, zda je jazyk dostupny/povoleny
	 * Pokud je seznam prazdny, je automaticky povolen
	 * @param   string  jazyk
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
 * Prelozi vyraz
 * @param   string  klic k prelozeni
 * @param   string  domena
 * @return  string
 */
function __($string, $domain = null)
{
	return L10n::__($string, $domain);
}



/**
 * Prelozi vyraz s ohledem na mnozne cisla
 * @param   string  klic k prelozeni - en singular
 * @param   string  klic k prelozeni - en plural
 * @param   int     cislo
 * @param   string  domena
 * @return  string
 */
function __n($singular, $plural, $count, $domain = null)
{
	return L10n::__n($singular, $plural, $count, $domain = null);
}



L10n::initialize();