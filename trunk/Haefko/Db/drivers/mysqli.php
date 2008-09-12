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


class DbMysqliDriver implements IDbDriver
{

	public function connect(array $config)
	{
		$this->resource = @new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

		if (mysqli_connect_errno())
			throw new DbException(mysqli_connect_error());

		$this->resource->set_charset($config['encoding']);
	}


	public function query($sql)
	{
		$this->result = $this->resource->query($sql);

		if ($this->resource->errno)
			throw new DbSqlException($this->resource->error);

		return clone $this;
	}


	public function fetch($assoc)
	{
		return $this->result->fetch_array($assoc ? MYSQLI_ASSOC : MYSQLI_NUM);
	}


	public function escape($value, $type)
	{
		switch ($type) {
		case 'identifier':     return "`$value`";
		case 'text':           return "'" . $this->resource->escape_string($value) . "'";
		}
	}


	public function affectedRows()
	{
		return $this->resource->affected_rows;
	}


	public function columnsMeta()
	{
		$count = $this->result->field_count;

		$meta = array();
		for ($i = 0; $i < $count; $i++)
			$meta[] = (array) $this->result->fetch_field_direct($i);

		return $meta;
	}


	public function rowCount()
	{
		return $this->result->num_rows;
	}


}