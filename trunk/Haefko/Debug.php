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



/**
 * Trida Debug poskytuje sluzby okolo debugingu vasi aplikace
 */
class Debug
{



    public static $startTime;



    /**
     * Vytvori specialni debug listu s informace o prave probehlem rizeni aplikace
     * @return  void
     */
    public static function debugRibbon()
    {
        $app = Application::getInstance();

        if (class_exists('Db', false)) {
            $sql = Db::getDebug();
        } else {
            $sql = null;
        }

        $time = round((microtime(true) - self::$startTime) * 1000, 2);

        require_once $app->getCorePath() . '../Templates/debugRibbon.phtml';
    }



    /**
     * Vypise lidsky-citelny obsah a strukturu promenne
     * @param   mixed   promenna pro vypis
     * @return  void
     */
    public static function dump($var)
    {
        echo '<pre style="text-align: left;">' . htmlspecialchars(print_r($var, true)) . '</pre>';
    }



    /**
     * Zachyti vyjimky a zobrazi podrobny debug vypis
     * @param   Exception   nezachycena vyjimka
     * @return  void
     */
    public static function exceptionHandler(Exception $exception)
    {
        $trace = preg_replace('#(\[password\]\s=&gt;\s).+#mi', '$1*CHRANENO*', print_r($exception->getTrace(), true));
        $app = Application::getInstance();
        require_once $app->getCorePath() . '../Templates/debugException.phtml';
    }



    /**
     * Compatibility for Dibi
     */
    public static function addColophon()
    {}



    /**
     * Prevede pole do html reprezentace
     * @param   array   pole pro prevod
     * @return  string
     */
    public static function readableArray($array, $skip = array('password'))
    {
        $ret = "<ul>";
        foreach ($array as $key => $val) {
            if (in_array($key, $skip)) {
                continue;
            }

            $ret .= "<li>$key: ";
            if (is_array($val)) {
                $ret .= self::readableArray($val);
            } else {
                $ret .= $val;
            }
            $ret .= "</li>";
        }
        $ret .= "</ul>";
        return $ret;
    }



}


Debug::$startTime = microtime(true);