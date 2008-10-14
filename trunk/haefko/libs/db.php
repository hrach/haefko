<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko
 */


require_once dirname(__FILE__) . '/db/exceptions.php';


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
	 * @return  bool
	 */
	public static function connect($config = array(), $name = 'default')
	{
		if (empty($config))
			$config = Config::read('Db.connection', array());

		if (isset(self::$connections[$name]))
			throw new DbException("Connection '$name' is already created.");

		require_once dirname(__FILE__) . '/db/connection.php';
		self::$connections[$name] = new DbConnection($config);
		self::$active = $name;
		return true;
	}


	/**
	 * Set the connection $name as active
	 * @param   string  connection name
	 * @return  void
	 */
	public static function active($name)
	{
		if (!isset(self::$connections[$name]))
			throw new DbException("Connection '$name' doesn't exists.");

		self::$active = $name;
	}


	/**
	 * Wrapper for DbConnection::query()
	 * @param   string    sql query
	 * @return  DbResult
	 */
	public static function query($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'query'), $args);
	}


	/**
	 * Wrapper for DbConnection::rawQuery()
	 * @param   string    sql query
	 * @return  DbResult
	 */
	public static function rawQuery($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'rawQuery'), $args);
	}


	/**
	 * Wrapper for DbConnection::test()
	 * @param   string    sql query
	 * @return  DbResult
	 */
	public static function test($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'test'), $args);
	}


	/**
	 * Wrapper for DbConnection::fetchField()
	 * @param   string    sql query
	 * @return  mixed
	 */
	public static function fetchField($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'fetchField'), $args);
	}


	/**
	 * Return active connection
	 * @return  DbConnection
	 */
	public static function getConnection()
	{
		if (empty(self::$active) || !isset(self::$connections[self::$active]))
			throw new DbException('No database connection.');

		return self::$connections[self::$active];
	}


}