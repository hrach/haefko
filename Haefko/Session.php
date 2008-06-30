<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.6
 * @package     Haefko
 */



require_once dirname(__FILE__) . '/Http.php';



/**
 * Trida pro praci se session
 */
class Session
{



    private static $started = false;



    public static function start()
    {
        self::checkHeaders();
        session_start();
        self::$started = true;
    }



    public static function read($var)
    {
        if (!self::$started) {
            self::start();
        }

        if (isset($_SESSION[$var])) {
            return $_SESSION[$var];
        } else {
            false;
        }
    }



    public static function safeRead($var)
    {
        if (isset($_COOKIE[ini_get('session.name')])) {
            return self::read($var);
        }

        return false;
    }



    public static function exists($var)
    {
        if (!self::$started) {
            self::start();
        }

        return isset($_SESSION[$var]);
    }



    public static function write($var, $val)
    {
        if (!self::$started) {
            self::start();
        }

        $_SESSION[$var] = $val;
    }



    public static function delete($var)
    {
        if (!self::$started) {
            self::start();
        }

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

            $name    = 'hf-session';
            $expires = 3600;
            $path    = Http::$baseUri;
            $domain  = Http::getDomain();

            if (class_exists('Config', false)) {
                ini_set('session.save_path', Config::read('Session.temp', Application::getInstance()->getPath() . 'temp'));
                $name    = Config::read('Session.name', $name);
                $expires = Config::read('Session.expires', $expires);
                $path    = config::read('Session.path', $path);
                $domain  = config::read('Session.domain', $domain);
            }

            if (substr_count ($domain, ".") == 1) {
                $domain = '.' . $domain;
            } else {
                $domain = preg_replace ('/^([^.])*/i', null, $domain);
            }

            ini_set('session.name', $name);
            ini_set('session.cookie_lifetime', $expires);
            ini_set('session.cookie_path', $path);
            ini_set('session.cookie_domain', $domain);
        }
    }



    private static function checkHeaders()
    {
        if (headers_sent()) {
            throw new Exception("Sessions nelze zapnout, hlavicky byly jiz odeslany.");
        }
    }



}



Session::init();