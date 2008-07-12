<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.7
 * @package     Haefko
 */



/**
 * Lokalizacni trida
 */
class L10n
{


    public static $domain;
    public static $lang;

    private static $langs = array();



    /**
     * Nastavi vychozi hodnoty
     * @return
     */
    public static function initialize()
    {
        self::$lang = getenv("LANG");
        self::$langs = Config::read('L10n.langs', array());

        $setBy = Config::read('L10n.autodetect', 'config');

        if ($setBy == 'config')
            self::langByConfig();
        elseif ($setBy == 'browser')
            self::langByBrowser();
        elseif ($setBy == 'url')
            self::langByUrl(Config::read('L10n.url.var', 'lang'));

        self::domain('messages');
    }



    /**
     * Prelozi vyraz
     * @param   string  klic k prelozeni
     * @param   string  domena
     * @return  string
     */
    public static function translate($string, $domain = null)
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
    public static function ntranslate($singular, $plural, $count, $domain = null)
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
        $path = Application::getInstance()->getPath() . 'locale';
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
        if (!self::isAllowed($lang))
            return false;

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
                if (!self::isAllowed($match[1]))
                    continue;

                return self::lang($match[1]);
            }
        }

        return self::langByConfig();
    }



    /**
     * Nastavi jazyk podle konfigurace
     * @param   bool
     */
    public static function langByConfig()
    {
        return self::lang(Config::read('L10n.lang', 'cs'));
    }



    /**
     * Detekuje a nastavi jazyk podle url
     * @param   string  jmeno promenne s nazvem jazyka
     * @return  bool
     */
    public static function langByUrl($variable = 'lang')
    {
        if (isset(Router::$args[$variable]))
            return self::lang(Router::$args[$variable]);

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
function t($string, $domain = null)
{
    return L10n::translate($string, $domain);
}



/**
 * Prelozi vyraz s ohledem na mnozne cisla
 * @param   string  klic k prelozeni - en singular
 * @param   string  klic k prelozeni - en plural
 * @param   int     cislo
 * @param   string  domena
 * @return  string
 */
function nt($singular, $plural, $count, $domain = null)
{
    return L10n::ntranslate($singular, $plural, $count, $domain = null);
}



L10n::initialize();