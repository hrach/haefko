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



require_once dirname(__FILE__) . '/../Components/dibi.compact.php';
require_once dirname(__FILE__) . '/CustomModel.php';
require_once dirname(__FILE__) . '/../Config.php';



/**
 * Trida Db zapouzdruhe dibi knihovnu, pripoji se se spravnymi udaji
 */
class Db
{



    public static $debugSql = array();



    /**
     * Pripoji se k databazi
     * Pokud neni predan jako parametr pole pripojeni, je nacteno z konfiguracni direktivy 'Db.connection'
     * Priklad pro rozdilnou serverovou konfiguraci naleznete v manualu
     * @param   array   nastaveni pripojeni
     * @return  void
     */
    public static function connect(array $config = array())
    {
        if (empty($config))
            $config = Config::read('Db.connection', array());

        dibi::connect($config);

        if (Config::read('Core.debug', 0) > 1)
            dibi::addHandler(array('Db', 'sqlHandler'));
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
        if ($event == 'afterQuery')
            self::$debugSql[] = array(
                'sql' => dibi::$sql,
                'time' => dibi::$elapsedTime,
                'rows' => dibi::affectedRows(),
            );
    }



}