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



require_once dirname(__FILE__) . '/Strings.php';



/**
 * Trida Config ma na starosti konfiguraci aplikace
 */
class Config
{


    public static $spaces = "    ";
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

        if (self::read('Config.trim-www', true)) {
            $serverName = Strings::ltrim($serverName, 'www.');
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
        $data = self::parseFile($fileName);

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



    /**
     * Preparsuje yaml soubor (jen primitivni syntaxe!)
     * @param   string  cesta k souboru
     * @return  array
     */
    public static function parseFile($file)
    {
        $data = trim(file_get_contents($file));
        $data = preg_replace("#\t#", self::$spaces, $data);
        $data = explode("\n", $data);
        return self::parseNode($data);
    }



    /**
     * Parsuje uzel
     * @param   string  textovy blok
     * @return  array
     */
    protected static function parseNode($data)
    {
        $array = array();
        $skip = array();

        foreach ($data as $line => $node) {
            if (in_array($line, $skip)) continue;

            if (preg_match('#^(.+):\s*$#', $node, $match)) {
                $node = array();
                $i = $line + 1;

                while (isset($data[$i]) && substr($data[$i], 0, 4) == self::$spaces) {
                    $node[] = substr($data[$i], 4);
                    $skip[] = $i;
                    $i++;
                }

                $array[$match[1]] = self::parseNode($node);

            } elseif (preg_match('#^(.+):\s(.+)$#', $node, $match)) {
                $array[$match[1]] = $match[2];

            } else {
                die ('Haefko: spatny format konfigurace');

            }
        }

        return $array;
    }



}