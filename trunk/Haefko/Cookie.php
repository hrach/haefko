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
 * Trida pro praci s cookie
 */
class Cookie
{



    public static function read($var)
    {
        if (isset($_COOKIE[$var]))
            return $_COOKIE[$var];

        return false;
    }



    public static function exists($var)
    {
        return isset($_COOKIE[$var]);
    }



    public static function write($var, $val, $path = null, $domain = null)
    {
        self::checkHeaders();

        $expires = 3600;
        if (class_exists('Config', false))
            $expires = Config::read('Cookie.expires', $expires);

        setcookie($var, $val, time() + $expires, $path, $domain);
    }



    public static function delete($var, $path = null, $domain = null)
    {
        self::checkHeaders();
        setcookie($var, false, time() - 60000, $path, $domain);
    }



    private static function checkHeaders()
    {
        if (headers_sent())
            throw new Exception("Nelze nastavit cookie, hlavicky byly jiz odeslany.");
    }



}