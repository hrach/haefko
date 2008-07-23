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



require_once dirname(__FILE__) . '/functions.php';



/**
 * Trida Http je jedinecny pomocnik pri praci s hlavickymi a vsim okolo url
 */
class Http
{



    public static $serverUri;
    public static $baseUri;



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
     * Je stranka volana ajaxem
     * @return  bool
     */
    public static function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
            return true;

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
     * Vrati domenove jmeno / jmeno serveru
     * @return  string
     */
    public static function getDomain()
    {
        return $_SERVER['SERVER_NAME'];
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
        self::checkHeaders("Redirect $absoluteUrl");

        if (!in_array($code, $supportCode))
            throw new Exception("Nepodporovany typ presmerovani.");

        header("Location: $absoluteUrl", true, $code);
    }



    /**
     * Odesle hlavicku s mime-type
     * @param   string  mime-type
     * @return  void
     */
    public static function mimeType($mime)
    {
        self::checkHeaders("Mime $mime");
        header("Content-type: $mime");
    }



    /**
     * Zasle chybovou hlavicku
     * @param   int     cislo chybove hlavicky
     * @return  void
     */
    public static function error($code = 404)
    {
        self::checkHeaders("Error code $code");
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
        if ($method == 'post')
            $method = '_POST';
        else
            $method = '_GET';


        if (isset($$method[$var]))
            return $$method[$var];
        elseif(!isset($var))
            return $$method;
        else
            return null;
    }



    /**
     * Vraci pozadavek URL
     * @return  string
     */
    public static function getRequestUrl()
    {
        $url = urldecode($_SERVER['REQUEST_URI']);
        $url = strLeftTrim($url, dirname($_SERVER['SCRIPT_NAME']));

        return trim($url, '/\\');
    }



    public static function initialize()
    {
        self::sanitizeData();
        self::$baseUri = self::getBaseUri();
        self::$serverUri = self::getServerUri();
    }



    /**
     * Vrati zakladni uri webove aplikace
     * Nezavisle na subdomene
     * @return  string
     */
    private static function getBaseUri()
    {
        $base = trim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        if (empty($base))
            return '/';
        else
            return "/$base/";
    }



    /**
     * Vrati absolutni base url
     * @return  string
     */
    private static function getServerUri()
    {
        return 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '') . '://' . self::getDomain();
    }



    /**
     * Zkontroluje, zda nebyly odeslany hlavicky
     * V pripade ze ano, script ukonci a vypise chybovou hlasku
     * @return  void
     */
    private static function checkHeaders($data = null)
    {
        if (headers_sent())
            throw new Exception("Nelze zaslat hlavicku, hlavicky byly jiz odeslany. Data: $data");
    }



}



Http::initialize();