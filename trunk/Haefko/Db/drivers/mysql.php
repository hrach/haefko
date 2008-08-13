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



class DbMysqlDriver implements IDbDriver
{



	public function connect(array $config)
	{
		$this->resource = @mysql_connect($config['server'], $config['username'], $config['password']);

		if (!$this->resource)
			throw new DbException(mysql_error($this->resource));

		if (!mysql_select_db($config['database'], $this->resource))
			throw new DbException(mysql_error($this->resource));

		$this->query("set names '$config[encoding]'");
	}



	public function query($sql)
	{
		$this->result = @mysql_query($sql, $this->resource);

		if (mysql_errno($this->resource))
			throw new DbSqlException(mysql_error($this->resource)));

		return clone $this;
	}



	public function fetch($type)
	{
		return mysql_fetch_array($this->result, $type ? MYSQLI_ASSOC : MYSQLI_NUM);
	}



	public function quote($string, $type)
	{
		if ($type == 'value') 
			return "'$string'";
		else
			return "`$string`";
	}



	public function escape($string)
	{
		return mysql_real_escape_string($string, $this->resource);
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



}