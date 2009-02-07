<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Database
 */


class MysqlDbDriver extends DbDriver
{


	public function connect($config)
	{
		$this->resource = @mysql_connect($config['server'], $config['username'], $config['password']);

		if (!$this->resource)
			throw new Exception(mysql_error($this->resource));

		if (!mysql_select_db($config['database'], $this->resource))
			throw new Exception(mysql_error($this->resource));

		$this->query("set names '$config[encoding]'");
	}


	public function query($sql)
	{
		$this->result = @mysql_query($sql, $this->resource);

		if (mysql_errno($this->resource))
			throw new Exception(mysql_error($this->resource) . " ($sql).");

		return clone $this;
	}


	public function fetch($assoc)
	{
		$fetch = mysql_fetch_array($this->result, $assoc ? MYSQL_ASSOC : MYSQL_NUM);

		if ($fetch === false)
			return null;
		else
			return $fetch;
	}


	public function escape($type, $value)
	{
		switch ($type) {
			case 'column':
				return "`$value`";
			case 'text':
				return "'" . mysql_real_escape_string($value, $this->resource) . "'";
		}
	}


	public function affectedRows()
	{
		return mysql_affected_rows($this->resource);
	}


	public function rowCount()
	{
		return mysql_num_rows($this->result);
	}


	public function insertedId($sequence)
	{
		return mysql_insert_id($this->resource);
	}


	public function getTables()
	{
		return db::fetchPairs('SHOW TABLES');
	}


	public function getTableColumnsDescription($table)
	{
		$structure = array();
		foreach (db::fetchAll("DESCRIBE [$table]") as $row) {
			$type = $row->Type;
			$length = null;
			if (preg_match('#^(.*)\((\d+)\)$#', $row->Type, $match)) {
				$type = $match[1];
				$length = $match[2];
			}

			$structure[$row->Field]['null'] = $row->Null === 'YES';
			$structure[$row->Field]['primary'] = $row->Key === 'PRI';
			$structure[$row->Field]['length'] = $length;
			$structure[$row->Field]['type'] = $type;
		}

		return $structure;
	}


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