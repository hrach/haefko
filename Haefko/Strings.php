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
 * Trida Strings poskytuje funkce pro praci s retezci
 */
class Strings
{



    /**
     * Kamelizuje retezec
     * @param   string  retezec, ktery chcete kamelizovat
     * @return  string
     */
    public static function camelize($word)
    {
        return str_replace(' ', '', ucwords(str_replace(array('_', '-'), array(' ', ' '), $word)));
    }



    /**
     * Velka pismena prevede na male a vlozi pred ne podtrzitko
     * @param   string  retezec, ktery chcete prevest
     * @return  string
     */
    public static function dash($word)
    {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '-\\1', $word));
    }



    /**
     * Odstrani veskerou diakritiku z textu
     * @param   string  retezec pro prevod
     * @return  string
     */
    public static function toAscii($string)
    {
        if (defined('ICONV_IMPL') && ICONV_IMPL != 'libiconv') {
            /**
             * @author David GRUDL
             * @link   http://davidgrudl.cz
             */
            static $tbl = array("\xc3\xa1"=>"a","\xc3\xa4"=>"a","\xc4\x8d"=>"c","\xc4\x8f"=>"d","\xc3\xa9"=>"e",
                                "\xc4\x9b"=>"e","\xc3\xad"=>"i","\xc4\xbe"=>"l","\xc4\xba"=>"l","\xc5\x88"=>"n",
                                "\xc3\xb3"=>"o","\xc3\xb6"=>"o","\xc5\x91"=>"o","\xc3\xb4"=>"o","\xc5\x99"=>"r",
                                "\xc5\x95"=>"r","\xc5\xa1"=>"s","\xc5\xa5"=>"t","\xc3\xba"=>"u","\xc5\xaf"=>"u",
                                "\xc3\xbc"=>"u","\xc5\xb1"=>"u","\xc3\xbd"=>"y","\xc5\xbe"=>"z","\xc3\x81"=>"A",
                                "\xc3\x84"=>"A","\xc4\x8c"=>"C","\xc4\x8e"=>"D","\xc3\x89"=>"E","\xc4\x9a"=>"E",
                                "\xc3\x8d"=>"I","\xc4\xbd"=>"L","\xc4\xb9"=>"L","\xc5\x87"=>"N","\xc3\x93"=>"O",
                                "\xc3\x96"=>"O","\xc5\x90"=>"O","\xc3\x94"=>"O","\xc5\x98"=>"R","\xc5\x94"=>"R",
                                "\xc5\xa0"=>"S","\xc5\xa4"=>"T","\xc3\x9a"=>"U","\xc5\xae"=>"U","\xc3\x9c"=>"U",
                                "\xc5\xb0"=>"U","\xc3\x9d"=>"Y","\xc5\xbd"=>"Z");
            return strtr($string, $tbl);
        } else {
            return iconv("utf-8", "us-ascii//TRANSLIT", $string);
        }
    }



    /**
     * Vrati retezec vhody pro url
     * Preved vsechny znaky na mala pismena, vsechny ne-alfanumericke znaky nahradi pomlckou a odstrani jejich pripadnou duplicitu
     * V pripade nedostupnosti moderni knihovny libiconv funguje prevod pouze pr cesky a slovensky jazyk
     * @author  Jakub Vrana
     * @link    http://php.vrana.cz
     * @param   string  retezec, ktery chcete prevest
     * @return  string
     */
    public static function coolUrl($title) {
        $title = preg_replace('~[^\\pL0-9_]+~u', '-', $title);
        $title = trim($title, "-");
        $title = self::toAscii($title);
        $title = strtolower($title);
        $title = preg_replace('~[^-a-z0-9_]+~', '', $title);
        return $title;
    }



    /**
     * Osetri url, aby na zacatku a na konci nebyly lomitka
     * @param   string  url
     * @return  string
     */
    public static function sanitizeUrl($url)
    {
        return preg_replace('#\/+#', '/', trim($url, '/'));
    }



    /**
     * Prevede url na pole, jednotlive prvky url jsou rozdeleny pomoci lomitek
     * @param   string  url
     * @return  array
     */
    public static function urlToArray($url)
    {
        $url = self::sanitizeUrl($url);
        if (!empty($url)) {
            return explode('/', $url);
        } else {
            return array();
        }
    }



    /**
     * Odstrani ze zacatku retezce pozadovany subretezec
     * @param   string  retezec
     * @param   string  retezec ktery chtete odstranit
     * @return  string
     */
     public static function ltrim($string, $substring)
     {
        if (strpos($string, $substring) === 0) {
            return substr($string, strlen($substring));
        }

        return $string;
     }



    /**
     * Odstrani z konce retezce pozadovany subretezec
     * @param   string  retezec
     * @param   string  retezec ktery chtete odstranit
     * @return  string
     */
     public static function rtrim($string, $substring)
     {
        $i = strlen($substring);
        if (substr($string, -$i) == $substring) {
            return substr($string, 0, -$i);
        }

        return $string;
     }



}




if (!function_exists('lcfirst')) {

    /**
     * Prevede prvni pismeno retezce na male
     * @param   string  retezec
     * @return  string
     */
    function lcfirst($string)
    {
        $string[0] = strtolower($string[0]);
        return (string) $string;
    }

}



/**
 * Prevede php pole do js pole
 * @param   array   pole
 * @return  string
 */
function toJsArray(array $array)
{
    if (empty($array)) return '[]';
    return "['" . join("', '", $array) . "']";
}