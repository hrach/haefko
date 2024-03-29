<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
 * @subpackage  Database
 */


require_once dirname(__FILE__) . '/db/connection.php';


class Db
{

	/**#@+
	 * Column types
	 */
	const COLUMN = 'c'; 
	const RAW = 'r';
	const NULL = 'n';
	const TEXT = 's';
	const BINARY = 'bin';
	const BOOL = 'b';
	const INTEGER = 'i';
	const FLOAT = 'f';
	const TIME = 't';
	const DATE = 'd';
	const DATETIME = 'dt';
	const SET = 'set';
	const A_LIST = 'l';
	const A_ASSOC = 'a';
	const A_VALUES = 'v';
	const A_MULTI_VALUES = 'm';
	/**#@-*/

	/** @var array */
	public static $sqls = array();

	/** @var string */
	private static $active;

	/** @var array[string]DbConnection */
	private static $connections = array();


	/**
	 * Connects to database
	 * If you don't provide $config, its load from config directive Db.connection
	 * @link http://haefko.skrasek.com/database
	 * @param array $config connection config
	 * @param string $name connection name
	 * @return bool
	 */
	public static function connect($config = array(), $name = 'default')
	{
		if (isset(self::$connections[$name]))
			self::$active[$name];

		if (empty($config) && class_exists('Config', false))
			$config = Config::read('db.connection');

		self::$connections[$name] = new DbConnection($config);
		self::$active = $name;
		return true;
	}


	/**
	 * Actives the connection $name
	 * @param string $name connection name
	 */
	public static function active($name)
	{
		if (!isset(self::$connections[$name]))
			throw new Exception("Connection '$name' doesn't exists.");

		self::$active = $name;
	}


	/**
	 * Wrapper for active connection
	 * @see DbConnection::rawPrepare()
	 * @param string $sql sql query
	 * @return DbResult
	 */
	public static function execute($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'execute'), $args);
	}


	/**
	 * Wrapper for active connection
	 * @see DbConnection::prepare()
	 * @param string $sql sql query
	 * @return DbPreparedResult
	 */
	public static function prepare($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'prepare'), $args);
	}


	/**
	 * Wrapper for active connection
	 * @see DbConnection::query()
	 * @param string $sql sql query
	 * @return DbResult
	 */
	public static function query($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'query'), $args);
	}


	/**
	 * Wrapper for active connection
	 * @see DbConnection::fetchField()
	 * @param string $sql sql query
	 * @return mixed
	 */
	public static function fetchField($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'fetchField'), $args);
	}


	/**
	 * Wrapper for active connection
	 * @see DbConnection::fetch()
	 * @param string $sql sql query
	 * @return mixed
	 */
	public static function fetch($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'fetch'), $args);
	}


	/**
	 * Wrapper for active connection
	 * @see DbConnection::fetchAll()
	 * @param string $sql sql query
	 * @return mixed
	 */
	public static function fetchAll($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'fetchAll'), $args);
	}


	/**
	 * Wrapper for active connection
	 * @see DbConnection::fetchPairs()
	 * @param string $sql sql query
	 * @return mixed
	 */
	public static function fetchPairs($sql)
	{
		$args = func_get_args();
		return call_user_func_array(array(self::getConnection(), 'fetchPairs'), $args);
	}


	/**
	 * Wrapper for active connection
	 * @see DbConnection::affectedRows()
	 * @return int
	 */
	public static function affectedRows()
	{
		return call_user_func(array(self::getConnection(), 'affectedRows'));
	}


	/**
	 * Logs sql query to debugger. Works only when Db.debug is active
	 * @param string $sql sql query
	 * @param int $time microtime timestamp
	 */
	public static function debug($sql, $time)
	{
		if (!class_exists('Config', false) || !class_exists('Debug', false))
			return;

		if (Config::read('db.debug', 1) == 0)
			return;

		$abbr = 'time: ' . Debug::getTime($time) . 'ms; affected: ' . self::affectedRows();
		$text = "<abbr title=\"$abbr\">" . htmlspecialchars($sql) . '</abbr>';
		Debug::toolbar($text, 'sql');
	}


	/**
	 * Returns active connection
	 * @throws Exception
	 * @return DbConnection
	 */
	public static function getConnection()
	{
		if (empty(self::$active) || !isset(self::$connections[self::$active]))
			self::connect();

		return self::$connections[self::$active];
	}
	
	
	/**
	 * Returns db driver
	 * @return DbDriver
	 */
	public static function getDriver()
	{
	    $connection = self::getConnection();
	    return $connection->getDriver();
	}


}