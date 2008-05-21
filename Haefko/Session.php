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



require_once dirname(__FILE__) . '/Http.php';



class Session
{



    public static function start()
    {
        self::checkHeaders();
        self::init();
        session_start();
    }



    public static function read($var)
    {
        if (isset($_SESSION[$var])) {
            return $_SESSION[$var];
        } else {
            false;
        }
    }



    public static function exists($var)
    {
        return isset($_SESSION[$var]);
    }



    public static function write($var, $val)
    {
        $_SESSION[$var] = $val;
    }



    public static function delete($var)
    {
        unset($_SESSION[$var]);
    }



    public static function destroy()
    {
        session_destroy();
    }



    private static function init()
    {
        if (function_exists('ini_set')) {
            ini_set('session.use_cookies', 1);

            $sname    = 'hf-session';
            $sexpires = 3600;
            $spath    = Http::getInternalUrl();
            $sdomain  = Http::getDomain();

            if (class_exists('Config', false)) {
                ini_set('session.save_path', Config::read('Session.temp', Application::getInstance()->getPath() . 'temp'));
                $sname    = Config::read('Session.name', $sname);
                $sexpires = Config::read('Session.expires', $sexpires);
                $spath    = config::read('Session.path', $spath);
                $sdomain  = config::read('Session.domain', $sdomain);
            }

            if (substr_count ($sdomain, ".") == 1) {
                $sdomain = '.' . $sdomain;
            } else {
                $sdomain = preg_replace ('/^([^.])*/i', null, $sdomain);
            }

            ini_set('session.name', $sname);
            ini_set('session.cookie_lifetime', $sexpires);
            ini_set('session.cookie_path', $spath);
            ini_set('session.cookie_domain', $sdomain);
        }
    }



    private static function checkHeaders()
    {
        if (headers_sent()) {
            throw new Exception("Sessions nelze zapnout, hlavicky byly jiz odeslany.");
        }
    }



}



Session::start();