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



require_once dirname(__FILE__) . '/CustomModel.php';
require_once dirname(__FILE__) . '/../Config.php';
require_once dirname(__FILE__) . '/../Components/dibi.compact.php';



/**
 * Trida Db zapouzdruhe dibi knihovnu, pripoji se se spravnymi udaji
 */
class Db
{



    private static $debugSql = array();



    /**
     * Pripoji se k databazi
     * Pokud neni predan jako parametr pole pripojeni, je nacteno z konfiguracni direktivi 'Db.connection'
     * Priklad pro rozdilnou serverovou konfiguraci naleznete na adrese http://hf.programujte.com/manual/...
     * @param   array   nastaveni pripojeni
     * @return  void
     */
    public static function connect(array $config = array())
    {
        if (empty($config)) {
            $config = Config::read('Db.connection', array());
        }

        dibi::connect($config);

        if (Config::read('Core.debug', 0) > 1) {
            dibi::addHandler(array('Db', 'sqlHandler'));
        }
    }



    /**
     * Handler pro debug sql
     * @param   DibiConnection  pripojeni
     * @param   DibiEvent       zprava
     * @param	mixed           argument
     * @return  void
     */
    public static function sqlHandler($connection, $event, $arg)
    {
        if ($event == 'afterQuery') {
            self::$debugSql[] = array(
                'sql' => dibi::$sql,
                'time' => dibi::$elapsedTime,
                'rows' => dibi::affectedRows(),
            );
        }
    }



    /**
     * Vrati pole sql dotazu
     * @return  array
     */
    public static function getDebug()
    {
        return self::$debugSql;
    }



}