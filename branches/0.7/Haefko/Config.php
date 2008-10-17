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
 * Trida Config ma na starosti konfiguraci aplikace
 */
class Config
{

    /** @var array konfigurace */
    private static $config = array();



    /**
     * Zapise konfiguraci
     * @param   mixed   jmeno konfiguracni direktivy
     * @param   mixed   hodnota
     * @return  void
     */
    public static function write($var, $val)
    {
        if ($var == 'servers' && is_array($val)) {
            $sn = $_SERVER['SERVER_NAME'];

            if (self::read('Config.www', true))
                $sn = strLeftTrim($sn, 'www.');

            if (isset($val[$sn]))
                self::multiWrite($val[$sn]);
            else
                die("Haefko: Chybi klic '$sn' v multi-serverove konfiguraci.");
        } else {
            $levels = explode('.', $var);
            $level = & self::$config;

            foreach ($levels as $name) {
                if (!isset($level[$name]))
                    $level[$name] = array();

                $level = & $level[$name];
            }

            $level = $val;
        }
    }



    /**
     * Zpracuje hromadnou konfiguraci
     * @param   array   konfiguracni pole
     * @return  void
     */
    public static function multiWrite(array $configure)
    {
        foreach ($configure as $key => $val)
            self::write($key, $val);
    }



    /**
     * Parsuje konfiguraci v jazyce YAML
     * @param   string  jmeno konfiguracniho souboru
     * @return  void
     */
    public static function load($fileName)
    {
        self::multiWrite(self::parseFile($fileName));
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
        $levels = explode('.', $var);
        $level = & self::$config;

        foreach ($levels as $name) {
            if (isset($level[$name]))
                $level = & $level[$name];
            else
                return $default;
        }

        return $level;
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
        $data = preg_replace("#\t#", '    ', $data);
        $data = explode("\n", $data);
        return self::parseNode($data);
    }



    /**
     * Preparsuje uzel
     * @param   string  textovy blok
     * @return  array
     */
    protected static function parseNode($data)
    {
        $array = array();

        for ($i = 0,$to = count($data); $i < $to; $i++) {
            if (preg_match('#^([a-z0-9\-\.]+):(.*)$#Ui', trim($data[$i]), $match)) {
                if (empty($match[2])) {
                    $node = array();

                    while (isset($data[++$i]) && substr($data[$i], 0, 4) == '    ')
                        $node[] = substr($data[$i], 4);

                    --$i;
                    $array[$match[1]] = self::parseNode($node);
                } else {
                    if (preg_match('#\[[\'"](.+)[\'"](?:,\s[\'"](.+)[\'"])*\]#U', $match[2], $value))
                        array_shift($value);
                    else
                        $value = trim(trim($match[2]), '\'"');

                    $array[$match[1]] = $value;
                }
            }
        }

        return $array;
    }



}