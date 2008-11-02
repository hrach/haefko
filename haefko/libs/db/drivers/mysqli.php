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


/**
 * MySQLi driver
 * @subpackage  Database
 */
class MysqliDbDriver extends DbDriver
{

	public function connect($config)
	{
		$this->resource = @new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

		if (mysqli_connect_errno())
			throw new Exception(mysqli_connect_error());

		$this->resource->set_charset($config['encoding']);
	}


	public function query($sql)
	{
		$this->result = $this->resource->query($sql);

		if ($this->resource->errno)
			throw new Exception($this->resource->error . " ($sql).");

		return clone $this;
	}


	public function fetch($assoc)
	{
		return $this->result->fetch_array($assoc ? MYSQLI_ASSOC : MYSQLI_NUM);
	}


	public function escape($type, $value)
	{
		switch ($type) {
			case 'column':
				return "`$value`";
			case 'text':
				return "'" . $this->resource->escape_string($value) . "'";
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