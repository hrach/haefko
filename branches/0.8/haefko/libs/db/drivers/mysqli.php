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
			case 'bool':
				return $value ? 1 : 0;
		}
	}


	public function affectedRows()
	{
		return $this->resource->affected_rows;
	}


	public function rowCount()
	{
		return $this->result->num_rows;
	}


	public function insertedId($sequence)
	{
		return $this->resource->insert_id;
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
			if (preg_match('#^(.*)\((\d+)\)( unsigned)?$#', $row->Type, $match)) {
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
		$count = $this->result->field_count;

		$cols = array();
		for ($i = 0; $i < $count; $i++) {
			$col = $this->result->fetch_field_direct($i);
			$cols[] = array($col->table, $col->name);
		}

		return $cols;
	}


}