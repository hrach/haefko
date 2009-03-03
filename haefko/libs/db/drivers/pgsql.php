<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek, Miroslav Novy
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id: $
 * @package     Haefko_Database
 */


class PgsqlDbDriver extends DbDriver
{


	/** @var int */
	private static $insertedId;


	public function connect($config)
	{
		$pairs = array(
			'server' => 'host',
			'username' => 'user',
			'password' => 'password',
			'port' => 'port',
			'database' => 'dbname'
		);

		$connection = '';
		foreach ($pairs as $key => $val) {
			if (array_key_exists($key, $config))
				$connection .= " $val={$config[$key]}";
		}

		$this->resource = @pg_connect($connection);

		if ($this->resource === false)
			throw new Exception(pg_last_error());

		pg_set_client_encoding($this->resource, $config["encoding"]);
	}


	public function query($sql)
	{
		$this->result = @pg_query($this->resource, $sql);

		if ($this->result === false)
			throw new Exception(pg_last_error($this->resource) . " ($sql).");

		if (stripos('insert', $sql) === 0)
			self::$insertedId = pg_last_oid($this->resource);

		return clone $this;
	}


	public function fetch($assoc)
	{
		$fetch = pg_fetch_array($this->result, null, $assoc ? PGSQL_ASSOC : PGSQL_NUM);

		if ($fetch === false)
			return null;
		else
			return $fetch;
	}


	public function escape($type, $value)
	{
		switch ($type) {
			case 'column':
				return " $value ";
			case 'text':
				return "'" . pg_escape_string($this->resource, $value) . "'";
			case 'bool':
				return $value ? 1 : 0;
		}
	}


	public function affectedRows()
	{
		return pg_affected_rows($this->result);
	}


	public function rowCount()
	{
		return pg_num_rows($this->result);
	}


	public function insertedId($sequence)
	{
		return self::$insertedId;
	}

	
	public function getTables()
	{
		return db::fetchPairs("SELECT table_name FROM information_schema.tables "
							. "WHERE table_schema = 'public' AND table_type = 'BASE TABLE'"); 
	}


	public function getTableColumnsDescription($table)
	{
		$meta = db::fetchAll("SELECT a.attnum, a.attname AS field, t.typname AS type, a.attlen AS length, "
		                   . "a.atttypmod AS lengthvar, a.attnotnull AS null, p.contype AS keytype "
		                   . "FROM pg_type t, pg_class c, pg_attribute a LEFT JOIN pg_constraint p "
		                   . "ON p.conrelid = a.attrelid AND a.attnum = ANY (p.conkey) "
		                   . "WHERE c.relname = %s AND a.attnum > 0 AND a.attrelid = c.oid AND a.atttypid = t.oid "
		                   . "ORDER BY a.attnum", $table);

		$structure = array();
		foreach ($meta as $row) {
			$key = $row->pg_attribute->field;
			$type = $row->pg_type->type;
			$length = $row->pg_attribute->length > 0 ? $row->pg_attribute->length : $row->pg_attribute->lengthvar - 4;

			if (preg_match('#^(.*)\d+$#', $type, $match))
				$type = $match[1];
			
			$structure[$key] = array(
				'null' => $row->pg_attribute->null === 't',
				'primary' => $row->pg_constraint->keytype === 'p',
				'length' => $length,
				'type' => $type
			);
		}

		return $structure;
	}	


	public function getResultColomns()
	{
		$count = pg_num_fields($this->result);

		$cols = array();
		for ($i = 0; $i < $count; $i++) {
			$cols[] = array(
				pg_field_table($this->result, $i),
				pg_field_name($this->result, $i)
			);
		}

		return $cols;
	}	


} 