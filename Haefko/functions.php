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
 * Kamelizuje retezec
 * @param   string  retezec, ktery chcete kamelizovat
 * @return  string
 */
function strCamelize($word)
{
    return str_replace(' ', '', ucwords(str_replace(array('_', '-'), array(' ', ' '), $word)));
}



/**
 * Velka pismena prevede na male a vlozi pred ne podtrzitko
 * @param   string  retezec, ktery chcete prevest
 * @return  string
 */
function strDash($word)
{
    return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '-\\1', $word));
}



/**
 * Odstrani veskerou diakritiku z textu
 * @param   string  retezec pro prevod
 * @return  string
 */
function strToAscii($string)
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
function strToCoolUrl($title) {
    $title = preg_replace('~[^\\pL0-9_]+~u', '-', $title);
    $title = trim($title, "-");
    $title = strToAscii($title);
    $title = strtolower($title);
    $title = preg_replace('~[^-a-z0-9_]+~', '', $title);
    return $title;
}



/**
 * Osetri url, aby na zacatku a na konci nebyly lomitka
 * @param   string  url
 * @return  string
 */
function strSanitizeUrl($url)
{
    return preg_replace('#\/+#', '/', trim($url, '/'));
}



/**
 * Prevede url na pole, jednotlive prvky url jsou rozdeleny pomoci lomitek
 * @param   string  url
 * @return  array
 */
function strUrlToArray($url)
{
    $url = strSanitizeUrl($url);

    if (!empty($url))
        return explode('/', $url);
    else
        return array();
}



/**
 * Odstrani ze zacatku retezce pozadovany subretezec
 * @param   string  retezec
 * @param   string  retezec ktery chtete odstranit
 * @return  string
 */
function strLeftTrim($string, $substring)
{
    if (strpos($string, $substring) === 0)
        return substr($string, strlen($substring));

    return $string;
}



/**
 * Odstrani z konce retezce pozadovany subretezec
 * @param   string  retezec
 * @param   string  retezec ktery chtete odstranit
 * @return  string
 */
function strRightTrim($string, $substring)
{
    $i = strlen($substring);
    if (substr($string, -$i) == $substring)
        return substr($string, 0, -$i);

    return $string;
}



/**
 * Vrati pole
 * @return  array
 */
function a() {
    return func_get_args();
}



/**
 * Vytvori asociativni pole
 * @return array
 */
function aa() {
    $args = func_get_args();
    for ($l = 0, $c = count($args); $l < $c; $l++) {
        if ($l + 1 < count($args))
            $a[$args[$l]] = $args[$l + 1];
        else
            $a[$args[$l]] = null;

        $l++;
    }

    return $a;
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



if (!function_exists('json_encode')) {

    function json_encode($a = false)
    {
        if (is_null($a))
            return 'null';
        elseif ($a === false)
            return 'false';
        elseif ($a === true)
            return 'true';

        if (is_scalar($a)) {
            if (is_float($a)) {
                return floatval(str_replace(",", ".", strval($a)));
            } elseif (is_string($a)) {
                static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
            } else {
                return $a;
            }
        }

        $isList = true;
        for ($i = 0, reset($a), $countA = count($a); $i < $countA; $i++, next($a)) {
            if (key($a) !== $i) {
                $isList = false;
                break;
            }
        }

        $result = array();
        if ($isList) {
            foreach ($a as $v)
                $result[] = json_encode($v);

            return '[' . join(',', $result) . ']';
        } else {
            foreach ($a as $k => $v)
                $result[] = json_encode($k) . ':' . json_encode($v);

            return '{' . join(',', $result) . '}';
        }
    }

}

if (!function_exists('json_decode')) {

    function json_decode($data, $arrayOn = true)
    {

        static $protect = array(
            '#("[^:]*)\{(.*")#U' => '$1***lb***$2',
            '#("[^:]*)\}(.*")#U' => '$1***rb***$2',
            '#("[^:]*)\[(.*")#U' => '$1***la***$2',
            '#("[^:]*)\](.*")#U' => '$1***ra***$2'
        );

        $data = preg_replace(array_keys($protect), array_values($protect), $data);

        static $replace = array(
            '#\{#' => 'array(',
            '#\}#' => ')',
            '#([a-z0-9]+)\s*:#i' => '\'\1\' =>',
            '#"([^"]+)"\s*:#i' => '\'\1\' =>',
            '#\[#' => 'array(',
            '#\]#' => ')'
        );

        $data = preg_replace(array_keys($replace), array_values($replace), $data);

        static $repareProtect = array(
            '#\*\*\*lb\*\*\*#' => '{',
            '#\*\*\*rb\*\*\*#' => '}',
            '#\*\*\*la\*\*\*#' => '[',
            '#\*\*\*ra\*\*\*#' => ']'
        );

        $data = preg_replace(array_keys($repareProtect), array_values($repareProtect), $data);
        $data = "\$data = $data;";

        eval($data);
        return $data;

    }

}