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
 * Trida Debug poskytuje sluzby okolo debugingu vasi aplikace
 */
class Debug
{

    /** @var float */
    public static $startTime;



    /**
     * Vytvori specialni debug listu s informace o prave probehlem rizeni aplikace
     * @return  void
     */
    public static function debugToolbar()
    {
        $app = Application::getInstance();

        $sql = array();
        if (class_exists('Db', false))
            $sql = Db::$sqls;

        require_once $app->corePath . 'Templates/debugToolbar.phtml';
    }



    /**
     * Vrati pocet mikrosekund od zacatku pozadavku
     * @return  float
     */
    public static function getTime()
    {
        return round((microtime(true) - self::$startTime) * 1000, 2);
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
        ob_clean();
        $app = Application::getInstance();
        require_once $app->corePath . 'Templates/debugException.phtml';
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
    public static function readableArray($array, $indent = 0)
    {
        $ret = null;
        $tab = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $indent);

        foreach ($array as $key => $val) {
            if (preg_match('#(pass(ord)?|passw)#i', $key))
                continue;

            $ret .= "$tab$key: ";

            if (is_array($val))
                $ret .= "<br />" . self::readableArray($val, $indent + 1);
            else
                $ret .= "<strong>$val</strong><br />";
        }

        return $ret;
    }



}



Debug::$startTime = microtime(true);