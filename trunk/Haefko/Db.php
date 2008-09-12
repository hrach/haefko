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


class Db
{

	/** @var array Debug of queries */
	public static $sqls = array();

	/** @var string Name of active connection */
	private static $active;

	/** @var array */
	private static $connections = array();


	/**
	 * Connect to database
	 * If you don't provide $config, its load from config directive 'Db.connection'
	 * @example http://haefko.programujte.com/manual
	 * @param   array   connection config
	 * @param   array   connection name
	 * @return  void
	 */
	public static function connect($config = array(), $name = 'default')
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
	 * Set the connection $name as active
	 * @param   string  connection name
	 * @return  void
	 */
	public static function active($name)
	{
		if (isset(self::$connections[$name]))
			self::$active = $name;
		else
			throw new DbException("Connetion '$name' doesn't exists!");
	}


	/**
	 * Wrapper for DbConnection::query()
	 * @param   string    sql query
	 * @return  DbResult
	 */
	public static function query($sql)
	{
		self::checkConnection();
		$args = func_get_args();
		return call_user_func_array(array(self::$connections[self::$active], 'query'), $args);
	}



	/**
	 * Wrapper for DbConnection::fetchField()
	 * @param   string    sql query
	 * @return  mixed
	 */
	public static function fetchField($sql)
	{
		self::checkConnection();
		$args = func_get_args();
		return call_user_func_array(array(self::$connections[self::$active], 'fetchField'), $args);
	}


	/**
	 * Wrapper for DbConnection::fetch()
	 * @param   string    sql query
	 * @return  DbResultNode
	 */
	public static function fetch($sql)
	{
		self::checkConnection();
		$args = func_get_args();
		return call_user_func_array(array(self::$connections[self::$active], 'fetch'), $args);
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
		 call_user_func_array(array(self::$connections[self::$active], 'query'), $args);
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