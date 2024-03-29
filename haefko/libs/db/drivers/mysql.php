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


class MysqlDbDriver extends Object implements IDbDriver
{

	/** @var Resource */
	protected $connection;

	/** @var Resource */
	protected $result;


	/**
	 * Connects to database
	 * @param   array     configuration
	 * @throws  Exception
	 * @return  void
	 */
	public function connect($config)
	{
		$this->connection = @mysql_connect($config['server'], $config['username'], $config['password']);

		if (!$this->connection)
			throw new Exception(mysql_error());

		if (!mysql_select_db($config['database'], $this->connection))
			throw new Exception(mysql_error($this->connection));

		$this->query("set names '$config[encoding]'");
	}


	/**
	 * Runs native sql query
	 * @param   string    sql query
	 * @throws  Exception
	 * @return  DbDriver  clone $this
	 */
	public function query($sql)
	{
		$this->result = @mysql_query($sql, $this->connection);

		if (mysql_errno($this->connection))
			throw new Exception(mysql_error($this->connection) . " ($sql).");

		return clone $this;
	}


	/**
	 * Fetchs one result's row
	 * @param   bool      true = associative array | false = array
	 * @return  array
	 */
	public function fetch($assoc)
	{
		$fetch = mysql_fetch_array($this->result, $assoc ? MYSQL_ASSOC : MYSQL_NUM);

		if ($fetch === false)
			return null;
		else
			return $fetch;
	}


	/**
	 * Escapes $value as a $type
	 * @param   strign    type
	 * @param   strign    value
	 * @return  string
	 */
	public function escape($type, $value)
	{
		switch ($type) {
			case Db::COLUMN:
				if (strpos($value, '.') === false) {
					return "`$value`";
				} else {
					list($table, $column) = explode('.', $value);
					return "`$table`" . ($column == '*' ? '.*' : ".`$column`");
				}

			case Db::TEXT:
				return "'" . mysql_real_escape_string($value, $this->connection) . "'";
			
			case Db::BINARY:
				return "'" . mysql_real_escape_string($value, $this->connection) . "'";

			case Db::BOOL:
				return $value ? 1 : 0;

			case Db::TIME:
				return date("'H:i:s'", $value);

			case Db::DATE:
				return date("'Y-m-d'", $value);

			case Db::DATETIME:
				return date("'Y-m-d H:i:s'", $value);

			default:
				throw new InvalidArgumentException('Unknown column type.');
		}
	}


	/**
	 * Returns number of affected rows
	 * @return  int
	 */
	public function affectedRows()
	{
		return mysql_affected_rows($this->connection);
	}


	/**
	 * Counts rows in result
	 * @return  int
	 */
	public function rowCount()
	{
		return mysql_num_rows($this->result);
	}


	/**
	 * Returns last inserted id
	 * @return  int
	 */
	public function insertedId()
	{
		return mysql_insert_id($this->connection);
	}


	/**
	 * Returns list of tables
	 * @return  array
	 */
	public function getTables()
	{
		return db::fetchPairs('SHOW TABLES');
	}


	/**
	 * Returns description of table columns
	 * @param   string    table name
	 * @return  array
	 */
	public function getTableColumnsDescription($table)
	{
		$structure = array();
		foreach (db::fetchAll("DESCRIBE [$table]") as $row) {
			$type = $row->Type;
			$length = null;
			if (preg_match('#^(.*)\((\d+)\)( unsigned)?$#', $row->Type, $match)) {
				$type = $match[1];
				$length = $match[2];
			} elseif (preg_match('#^(enum|set)\((.+)\)$#', $row->Type, $match)) {
				$type = $match[1];
				$length = array();
				foreach (explode(',', $match[2]) as $val)
					$length[] = substr($val, 1, -1);
			}

			$structure[$row->Field]['null'] = $row->Null === 'YES';
			$structure[$row->Field]['primary'] = $row->Key === 'PRI';
			$structure[$row->Field]['length'] = $length;
			$structure[$row->Field]['type'] = $type;
		}

		return $structure;
	}


	/**
	 * Returns result columns
	 * @return  array
	 */
	public function	getResultColomns()
	{
		$count = mysql_num_fields($this->result);

		$cols = array();
		for ($i = 0; $i < $count; $i++) {
			$col = mysql_fetch_field($this->result, $i);
			$cols[] = array($col->table, $col->name);
		}

		return $cols;
	}


}