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
			throw new Exception(mysql_error($this->resource));

		return clone $this;
	}


	public function fetch($assoc)
	{
		$fetch = mysql_fetch_array($this->result, $assoc ? MYSQLI_ASSOC : MYSQLI_NUM);

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


	public function columnsMeta()
	{
		$count = mysql_num_fields($this->result);

		$meta = array();
		for ($i = 0; $i < $count; $i++)
			$meta[] = (array) mysql_fetch_field($this->result, $i);

		return $meta;
	}


	public function rowCount()
	{
		return mysql_num_rows($this->result);
	}


	public function insertedId($sequence)
	{
		return mysql_insert_id($this->resource);
	}


}