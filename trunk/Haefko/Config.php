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
 * Trida Config ma na starosti konfiguraci aplikace
 */
class Config
{



    private static $config = array();



    /**
     * Zapise konfiguraci
     * @param   mixed   jmeno konfiguracni direktivy
     * @param   mixed   hodnota
     * @return  void
     */
    public static function write($var, $val)
    {
        if (!empty($var)) {
            self::$config[$var] = $val;
        }
    }



    /**
     * Vybere se jeho odpovidaji klic (nazev domeny) z $configure
     * Hodnota klice (pole) je dale zpracovano jako klasicka konfigurace
     * @param   array   konfiguracni pole
     * @return  void
     */
    public static function multiWrite(array $configure)
    {
        $serverName = $_SERVER['SERVER_NAME'];

        if (self::read('Config.trim-www', false)) {
            Strings::ltrim($serverName, 'www.');
        }

        if (isset($configure[$serverName]) && is_array($configure[$serverName])) {
            foreach ($configure[$serverName] as $key => $val) {
                self::$config[$key] = $val;
            }
        }
    }



    /**
     * Parsuje konfiguraci v jazyce YAML
     * @param   string  jmeno konfiguracniho souboru
     * @return  void
     */
    public static function load($fileName)
    {
        require_once dirname(__FILE__) . '/Components/spyc.php';

        $data = Spyc::YAMLLoad($fileName);
        foreach ($data as $key => $val) {
            if ($key == 'multi' && is_array($val)) {
                self::multiWrite($val);
            } else {
                self::write($key, $val);
            }
        }
    }



    /**
     * Precte konfiguraci
     * Pokud nebyla direktiva jeste nastavena, vrati metoda druhy argument
     * @param   string  jmeno direktivy
     * @param   mixed   defaultni hodnota
     * @return  mixed
     */
    public static function read($var, $default = false)
    {
        if (isset(self::$config[$var])) {
            return self::$config[$var];
        } else {
            return $default;
        }
    }



    /**
     * Vrati pole s celou konfiguraci
     * @return  array
     */
    public static function getConfig()
    {
        return self::$config;
    }



}