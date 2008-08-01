<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.8
 * @package     Haefko
 */



/**
 * Trida Db zapouzdruhe dibi knihovnu, pripoji se se spravnymi udaji
 */
class Db
{

	/** @var array Debug provedenych sql dotazu */
	public static $sqls = array();

	/** @var string Jmeno aktivniho pripojeni */
	private static $active;

	/** @var array Pripojeni */
	private static $connections = array();



	/**
	 * Pripoji se k databazi
	 * Pokud neni predan jako parametr pole pripojeni, je nacteno z konfiguracni direktivy 'Db.connection'
	 * Priklad pro rozdilnou serverovou konfiguraci naleznete v manualu
	 * @param   array   nastaveni pripojeni
	 * @param   array   jmeno pripojeni
	 * @return  void
	 */
	public static function connect(array $config = array(), $name = 'default')
	{
		if (empty($config))
			$config = Config::read('Db.connection', array());

		if (!isset(self::$connections[$name])) {
			require_once dirname(__FILE__) . '/Db/DbConnection.php';
			self::$connections[$name] = new DbConnection($config);
			self::$active = $name;
		}
	}


	public static function query($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::$connections[self::$active], 'query'), $args);
	}



	public static function fetchField($sql)
	{
		$args = func_get_args();
		$result = call_user_func_array(array(self::$connections[self::$active], 'query'), $args);
		return $result->fetchField();
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
			self::$sqls[] = array(
				'sql' => htmlspecialchars(dibi::$sql),
				'time' => dibi::$elapsedTime,
				'rows' => dibi::affectedRows(),
			);
	}



}



//if (Config::read('Core.debug', 0) > 1)
//	dibi::addHandler(array('Db', 'sqlHandler'));