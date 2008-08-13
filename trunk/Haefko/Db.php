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
	 * Pokud neni predano konfiguracni pole pripojeni, je nacteno z konfiguracni direktivy 'Db.connection'
	 * @example http://haefko.programujte.com/manual
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



	/**
	 * Aktivuje pripojeni $name za vyhozi
	 * @param   string  jmeno pripojeni
	 * @return  void
	 */
	public static function active($name)
	{
		if (isset(self::$connections[$name]))
			self::$active = $name;
		else
			throw new DbException("Db: Connetion '$name' doesn't exists!");
	}


	/**
	 * Provede dotaz na aktivni pripojeni
	 * @param   string  sql dotaz
	 * @return  DbResult
	 */
	public static function query($sql)
	{
		self::checkConnection();
		$args = func_get_args();
		return call_user_func_array(array(self::$connections[self::$active], 'query'), $args);
	}



	/**
	 * Provede dotaz na aktivni pripojeni a vrati prvni pole prvniho zaznamu
	 * @param   string  sql dotaz
	 * @return  mixed
	 */
	public static function fetchField($sql)
	{
		self::checkConnection();
		$args = func_get_args();
		$result = call_user_func_array(array(self::$connections[self::$active], 'query'), $args);
		return $result->fetchField();
	}



	/**
	 * Provede dotaz na aktivni pripojeni a vrati jeho prvni radek
	 * @param   string  sql dotaz
	 * @return  DbResultNode
	 */
	public static function fetch($sql)
	{
		self::checkConnection();
		$args = func_get_args();
		$result = call_user_func_array(array(self::$connections[self::$active], 'query'), $args);
		return $result->fetch();
	}



	/**
	 * Provede dotaz na aktivni pripojeni a vrati pole s jednolitvymi radky
	 * @param   string  sql dotaz
	 * @return  array   pole s DbResultNode
	 */
	public static function fetchAll($sql)
	{
		self::checkConnection();
		$args = func_get_args();
		$result = call_user_func_array(array(self::$connections[self::$active], 'query'), $args);
		return $result->fetchAll();
	}



	/**
	 * Zkontroluje, zda existuje spojeni s databazi
	 * return  void
	 */
	private static function checkConnection()
	{
		if (empty(self::$active) || !isset(self::$connections[self::$active]))
			throw new DbException('No connetion to database!');
	}



}