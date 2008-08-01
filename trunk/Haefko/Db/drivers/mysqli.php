<?php


class DbMysqliDriver implements IDbDriver
{



	public function connect(array $config)
	{
		$this->resource = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

		if(mysqli_connect_errno())
			throw new Exception('connect databases faild!');

		$this->resource->set_charset($config['encoding']);
	}


	public function affectedRows()
	{
		return $this->resource->affected_rows;
	}


	public function escape($string)
	{
		return $this->resource->escape_string($string);
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
		$count = $this->result->field_count;

		$meta = array();
		for ($i = 0; $i < $count; $i++)
			$meta[] = (array) $this->result->fetch_field_direct($i);

		return $meta;
	}


	public function fetch($type)
	{
		return $this->result->fetch_array($type ? MYSQLI_ASSOC : MYSQLI_NUM);
	}

	public function rowCount()
	{
		return mysqli_num_rows($this->result);
	}

	public function query($sql)
	{
		$this->result = $this->resource->query($sql);

		if ($this->resource->errno)
			throw new Exception('Query error!' . $this->resource->error);

		return $this;
	}



}