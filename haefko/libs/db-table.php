<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko_Application
 * @subpackage  Database
 */


/**
 * Provide interface for fetching data from the db tables
 * @subpackage Database
 */
abstract class DbTable extends Object
{


	/** @var string */
	public static $table;

	/** @var array */
	private static $pk = array();

	/** @var bool */
	public static $initialized = false;

	/** @var mixed */
	private $pkVal;

	/** @var array */
	private $fields = array();

	/** @var array */
	private $fieldsMod = array();


	/**
	 * Initializes defaults values
	 * @return  void
	 */
	public static function initialize()
	{
		if (empty(self::$table))
			throw new Exception('Undefined table name.');

		self::$pk = DbTableStructure::getPk(self::$table);
		self::$initialized = true;
	}


	/**
	 * Selects $cols from table where primary key is $pk
	 * @param   mixed        primary key value
	 * @param   strin|array  selected columns
	 * @return  DbResultNode
	 */
	public static function get($pk, $cols = '*')
	{
		if (!self::$initialized)
			self::initialize();

		list($col, $mod) = self::getPK($pk);
		$cols = is_array($cols) ? implode(', ', $cols) : $cols;
		return db::query("select $cols from [" . self::$table . "]"
		               . "where [$col] = $mod limit 1", $pk)->fetch();
	}


	/**
	 * Selects $cols from table where $col is $val
	 * @param   string       column
	 * @param   mixed        value
	 * @param   strin|array  selected columns
	 * @return  DbResultNode
	 */
	public static function getBy($col, $val, $cols = '*')
	{
		if (!self::$initialized)
			self::initialize();

		$mod = DbTableStructure::getMod(self::$table, $col);
		$cols = is_array($cols) ? implode(', ', $cols) : $cols;
		return db::query("select $cols from [" . self::$table . "]"
		               . "where [$col] = $mod limit 1", $val)->fetch();
	}


	/**
	 * Constructor
	 * @param   string    primary key value
	 * @return  void
	 */
	public function __construct($pkVal = null)
	{
		self::initialize();
		$this->pkVal = $pkVal;
	}


	/**
	 * Imports data from array
	 * @param   array     column => value
	 * @return  DbTable   $this
	 */
	public function import(array $data)
	{
		foreach ($data as $method => $args)
			call_user_func_array(array($this, 'set' . $method), $args);

		return $this;
	}


	/**
	 * Magic caller to sets the column value
	 * @param   string    method name
	 * @param   array     array of arguments
	 * @throws  BadMethodCallException
	 * @return  DbTable   $this
	 */
	public function __call($method, $args)
	{
		if (Tools::startWith($method, 'set')) {
			$column = Tools::underscore(str_replace('_', '.', Tools::lTrim($method, 'set')));
			if (empty($this->pkVal) && isset(self::$pk[$column])) {
			# primary key
				$this->pkVal = array_shift($args);
			} else {
			# other columns
				if (strpos($column, '.') === false)
					$column = self::$table . ".$column";

				$this->fields[$column] = array_shift($args);
				$this->fieldsMod[$column] = array_shift($args);
			}

			return $this;
		}

		throw new BadMethodCallException("Undefined method DbTable::$method().");
	}


	/**
	 * Saves (inserts|updates) db row
	 * @return  mixed     primary key's value
	 */
	public function save()
	{
		$fields = array();
		foreach ($this->fields as $column => $field) {
			if ($field instanceof DbTable)
				$fields[$column . $this->getMod($column)] = $field->save();
			else
				$fields[$column . $this->getMod($column)] = $field;
		}

		if (empty($this->pkVal)) {
			db::query('INSERT INTO [' . self::$table . '] %v', $fields);
		} else {
			list($col, $mod) = self::getPK($this->pkVal);
			$this->pkVal = db::query('UPDATE [' . self::$table . '] SET %a', $fields, " WHERE [$col] = $mod", $this->pkVal);
		}

		$this->fields = array();
		return $this->pkVal;
	}


	/**
	 * Returns modificator for column
	 * @param   string    column name
	 * @return  string
	 */
	private function getMod($column)
	{
		if (isset($this->fieldsMod[$column][0]))
			return $this->fieldsMod[$column][0];

		$parts = explode('.', $column);
		if (count($parts) > 1)
			return DbTableStructure::getMod($parts[0], $parts[1]);
		else
			return DbTableStructure::getMod(self::$table, $parts[0]);
	}


	/**
	 * Returns the primary column
	 * If table has more primary keys, then selects the right by the types of variable $pk
	 * @param   mixed       primary key value
	 * @throws  Exception
	 * @return  array       column name, modificator
	 */
	private static function getPK($pk)
	{
		if (empty(self::$pk))
			throw new Exception('Table ' . self::$talbe . ' has not the primary key.');

		reset(self::$pk);

		if (count(self::$pk) != 1) {
			$m = db::getConnection()->getType($pk);
			foreach (self::$pk as $column => $mod) {
				if ($mod == $m)
					return array($column, $m);
			}
		}

		return array(key(self::$pk), current(self::$pk));
	}


}