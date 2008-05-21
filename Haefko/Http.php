<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://hf.programujte.com
 * @version     0.6 alfa
 * @package     HF
 */



require_once dirname(__FILE__) . '/Strings.php';



/**
 * Trida Http je jedinecny pomocnik pri praci s hlavickymi a vsim okolo url
 */
class Http
{



    /**
     * Pokud je treba, odstrani automaticky magic quotes z $_GET, $_POST, $_COOKIE a $_REQUEST
     * @return  void
     */
    public static function sanitizeData()
    {
        if (get_magic_quotes_gpc()) {
            $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
            while (list($key, $val) = each($process)) {
                foreach ($val as $k => $v) {
                    unset($process[$key][$k]);
                    if (is_array($v)) {
                        $process[$key][$k] = $v;
                        $process[] = &$process[$key][$k];
                    } else {
                        $process[$key][$k] = stripslashes($v);
                    }
                }
            }
            unset($process);
        }
    }



    /**
     * Je stranka volana ajaxem?
     * @return  bool
     */
    public static function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            return true;
        }
        return false;
    }



    /**
     * Vrati IP uzivatele
     * @return  string
     */
    public static function getIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }



    /**
     * Vrati REQUEST_METHOD - hondnotu prevede na mala pismena
     * @return  string
     */
    public static function getRequestMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }



    /**
     * Vrati zakladni url pro tvorbu internich odkazu
     * Priklady:    aplikace bezi na                url
     *              ----------------------------------------
     *              example.com                     /
     *              test.example.com                /
     *              example.com/test                /test/
     * @return  string
     */
    public static function getInternalUrl()
    {
        $base = Strings::sanitizeUrl(dirname($_SERVER['SCRIPT_NAME']));

        if (empty($base)) {
            return '/';
        } else {
            return '/' . $base . '/';
        }
    }



    /**
     * Vrati domenove jmeno / jmeno serveru
     * @return  string
     */
    public static function getDomain()
    {
        return $_SERVER['SERVER_NAME'];
    }



    /**
     * Vrati absolutni base url
     * @return  string
     */
    public static function getServerUrl()
    {
        return 'http:' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '') . '//' . self::getDomain();
    }



    /**
     * Zasle presmerovaci hlavicku
     * @param   string  absolutni url, na ktere chcete presmerovat
     * @param   int     cislo presmerovaci hlavicky
     * @return  void
     */
    public static function redirect($absoluteUrl, $code = 300)
    {
        static $supportCode = array(300, 301, 302, 303, 304, 307);

        self::checkHeaders();

        if (!in_array($code, $supportCode)) {
            throw new Exception("Nepodporovany typ presmerovani.");
        }

        header('Location: ' . $absoluteUrl, true, $code);
    }



    /**
     * Odesle hlavicku s mime-type
     * @param   string  mime-type
     * @return  void
     */
    public static function mimeType($mime)
    {
        self::checkHeaders();
        header("Content-type: $mime");
    }



    /**
     * Zasle chybovou hlavicku
     * @param   int     cislo chybove hlavicky
     * @return  void
     */
    public static function error($code = 404)
    {
        self::checkHeaders();
        switch ($code) {
            case 401:
                header('HTTP/1.1 401 Unauthorized');
            break;
            case 404:
                header('HTTP/1.1 404 Not Found');
            break;
            case 500:
                header('HTTP/1.1 500 Internal Server Error');
            break;
            default:
                throw new Exception("Nepodporovany typ chybove hlavicky.");
            break;
        }
    }



    /**
     * Vrati hodnotu promenne predane pomoci $method, nebo vraci cele pole parametru metody
     * @param   string  jmeno promenne
     * @param   string  metoda (post|get)
     * @return  mixed
     */
    public static function getParam($var = null, $method = 'post')
    {
        if ($method == 'post') {
            $method = '_POST';
        } else {
            $method = '_GET';
        }

        if (isset($$method[$var])) {
            return $$method[$var];
        } elseif(!isset($var)) {
            return $$method;
        } else {
            return null;
        }
    }



    /**
     * Vraci pozadavek z URL
     * @return  string
     */
    public static function getRequestUrl()
    {
        $url = $_SERVER['REQUEST_URI'];
        Strings::ltrim($url, dirname($_SERVER['SCRIPT_NAME']));
        Strings::ltrim($url, '/' . basename($_SERVER['SCRIPT_NAME']));

        return Strings::sanitizeUrl($url);
    }



    /**
     * Zkontroluje, zda nebyly odeslany hlavicky
     * V pripade ze ano, script ukonci a vypise chybovou hlasku
     * @return  void
     */
    private static function checkHeaders()
    {
        if (headers_sent()) {
            throw new Exception("Presmerovani nelze provest, hlavicky byly jiz odeslany.");
        }
    }



}



Http::sanitizeData();