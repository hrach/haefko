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
		@mysql_select_db($config['database']);

		if (mysql_errno($this->resource))
			throw new Exception('connect databases faild!');

		@mysql_query("SET NAMES '$config[encoding]'", $this->resource);
	}



	public function escape($string)
	{
		return mysql_real_escape_string($string, $this->resource);
	}



	public function quote($string, $type)
	{
		if ($type == 'value') 
			return "'$string'";
		else
			return "`$string`";
	}



	public function getColumnsMeta()
	{
		$count = mysql_num_fields($this->result);

		$meta = array();
		for ($i = 0; $i < $count; $i++)
			$meta[] = (array) mysql_fetch_field($this->result, $i);

		return $meta;
	}



	public function fetch($type)
	{
		return mysql_fetch_array($this->result, $type ? MYSQLI_ASSOC : MYSQLI_NUM);
	}



	public function rowCount()
	{
		return mysqli_num_rows($this->result);
	}



	public function query($sql)
	{
		$this->result = mysql_query($sql, $this->resource);

		if (mysql_errno($this->resource))
			throw new Exception('Query error!');

		return clone $this;
	}



}