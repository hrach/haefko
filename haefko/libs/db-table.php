<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Application
 * @subpackage  Database
 */


require_once dirname(__FILE__) . '/tools.php';
require_once dirname(__FILE__) . '/db-structure.php';


abstract class DbTable extends Object
{


	/** @var DbStructure */
	public static $structure;

	/**
	 * Creates table class and returns class name
	 * @param   string  table name
	 * @return  string
	 */
	public static function init($name)
	{
		$class = Tools::camelize($name) . 'Table';
		if (!class_exists($class, false))
			eval("class $class extends DbTable {}");

		return $class;
	}


	/** @var string */
	protected $table;

	/** @var mixed */
	protected $primaryKey;

	/** @var mixed */
	protected $primaryKeyValue;

	/** @var array */
	protected $fields = array();

	/** @var array */
	protected $fieldsModificators = array();


	/**
	 * Constructor
	 * @param   string    primary key value
	 * @throws  Exception
	 * @return  void
	 */
	public function __construct($primaryKeyValue = null)
	{
		if (empty($this->table)) {
			$table = Tools::rTrim($this->getClass(), 'Table');
			$table = Tools::underscore($table);
			$this->table = $table;
		}

		if (!self::$structure->tableExists($this->table))
			throw new Exception("Db table \"{$this->table}\" doesn't exists.");


		$this->primaryKey = self::$structure->getPrimaryKey($this->table);
		$this->primaryKeyValue = $primaryKeyValue;
	}


	/**
	 * Selects $cols from table
	 * @param   string|array  selected columns
	 * @return  DbResultNode
	 */
	public function get($cols = '*')
	{
		$cols = $this->toSqlCols($cols);
		$mod = self::$structure->getModificator($this->table, $this->primaryKey);
		return Db::fetch("SELECT $cols FROM %c WHERE %c = $mod LIMIT 1", $this->table, $this->primaryKey, $this->primaryKeyValue);
	}


	/**
	 * Selects $cols from table by $col
	 * @param   string        column
	 * @param   string|array  selected columns
	 * @return  DbResultNode
	 */
	public function getBy($col, $cols = '*')
	{
		$cols = $this->toSqlCols($cols);
		$mod = self::$structure->getModificator($this->table, $col);
		return Db::fetch("SELECT $cols FROM %c WHERE %c = $mod LIMIT 1", $this->table, $col, $this->primaryKeyValue);
	}


	/**
	 * Returns table form
	 * @param   array    cols for edit
	 * @param   array    cols labels
	 * @throws  Exception
	 * @return  Form
	 */
	public function getForm($editCols = array(), $labels = array())
	{
		$form = new Form();
		$cols = self::$structure->getCols($this->table);

		foreach ($cols as $name => $data) {
			if (!empty($editCols) && !in_array($name, $editCols))
				continue;

			if ($data["primary"])
				continue;

			$label = isset($labels[$name]) ? $labels[$name] : null;


			switch ($data['type']) {
			case 'text':
			case 'longtext':
			case 'tinytext':
			case 'mediumtext':
				$form->addTextarea($name, $label);
				$form[$name]->addRule(Rule::LENGTH, array(0, $data['length']));
				if (!$data['null'])
					$form[$name]->addRule(Rule::FILLED);

				break;

			case 'tinyint':
			case 'mediumint':
			case 'bigint':
			case 'smallint':
			case 'int':
				$form->addText($name, $label);
				$form[$name]->addRule(Rule::INTEGER);
				break;

			case 'double':
			case 'decimal':
			case 'float':
				$form->addText($name, $label);
				$form[$name]->addRule(Rule::FLOAT);

			case 'enum':
				$options = array();
				foreach ($data['length'] as $optLabel)
					$options[$optLabel] = ucfirst($optLabel);
				
				$form->addSelect($name, $options, $label);
				break;

			case 'set':
				$options = array();
				foreach ($data['length'] as $optLabel)
					$options[$optLabel] = ucfirst($optLabel);
				
				$form->addMultiCheckbox($name, $options, $label);
				break;

			case 'date':
				$form->addDatepicker($name, $label);
				if (!$data['null'])
					$form[$name]->addRule(Rule::FILLED);

				break;

			case 'datetime':
			case 'time':
			case 'varchar':
			default:

				$form->addText($name, $label);
				$form[$name]->addRule(Rule::LENGTH, array(0, $data['length']));
				if (!$data['null'])
					$form[$name]->addRule(Rule::FILLED);

				break;
			}
		}

		$form->addSubmit('submit', isset($labels['submit']) ? $labels['submit'] : null);
		return $form;
	}


	/**
	 * Sets column value
	 * @param   string  column name
	 * @param   mixed   column value
	 * @param   string  column modificator
	 */
	public function set($column, $value, $mod = null)
	{
		if ($this->primaryKey == $column) {
			$this->primaryKeyValue = $value;

		} else {
			if (strpos($column, '.') === false)
				$column = $this->table . ".$column";

			if (!empty($mod))
				$this->fieldsModificators[$column] = $mod;

			$this->fields[$column] = $value;
		}

		return $this;
	}


	/**
	 * Imports data from array
	 * @param   array     column => value
	 * @return  DbTable   $this
	 */
	public function import(array $data)
	{
		foreach ($data as $column => $value)
			$this->set($column, $value);

		return $this;
	}


	/**
	 * Saves (inserts|updates) db entry
	 * @return  mixed     primary key's value
	 */
	public function save()
	{
		$fields = array();
		foreach ($this->fields as $column => $field) {
			if (strpos($column, '%') === false)
				$column = $column . $this->getModificator($column);

			if ($field instanceof DbTable)
				$fields[$column] = $field->save();
			else
				$fields[$column] = $field;
		}

		if (empty($this->primaryKeyValue)) {
			$this->primaryKeyValue = Db::query('INSERT INTO %c %kv', $this->table, $fields);

		} else {
			$mod = self::$structure->getModificator($this->table, $this->primaryKey);
			Db::query("UPDATE %c SET %l WHERE %c = $mod", $this->table, $fields, $this->primaryKey, $this->primaryKeyValue);
		
		}

		$this->fields = array();
		return $this->primaryKeyValue;
	}


	/**
	 * Removes db entry
	 * @return  bool
	 */
	public function remove()
	{
		$mod = self::$structure->getModificator($this->table, $this->primaryKey);
		Db::query("DELETE FROM %c WHERE %c = $mod LIMIT 1", $this->table, $this->primaryKey, $this->primaryKeyValue);
		return Db::affectedRows() == 1;
	}


	/**
	 * 
	 * @param   string    method name
	 * @param   array     array of arguments
	 * @throws  BadMethodCallException
	 * @return  DbTable   $this
	 */
	public function __call($method, $args)
	{
		if (Tools::startWith($method, 'set')) {
			$column = Tools::lTrim($method, 'set');
			$column = str_replace('_', '.', $column);
			$column = Tools::underscore($column);
			
			return $this->set($column, array_shift($args), array_shift($args));
		}

		throw new BadMethodCallException("Undefined method DbTable::$method().");
	}


	/**
	 * Returns modificator for column
	 * @param   string    column name
	 * @return  string
	 */
	private function getModificator($column)
	{
		if (!empty($this->fieldsModificators[$column]))
			return '%' . $this->fieldsModificators[$column];

		$parts = explode('.', $column);
		if (count($parts) > 1)
			return self::$structure->getModificator($parts[0], $parts[1]);
		else
			return self::$structure->getModificator(self::$table, $parts[0]);
	}


	/**
	 * Transforms array of cols as sql string
	 * @param   string  cols
	 * @return  string
	 */
	private function toSqlCols($cols)
	{
		if (is_array($cols)) {
			$c = array();
			foreach ($cols as $col)
				$c[] = "[$col]";
			$cols = implode(', ', $c);
		}

		return $cols;
	}


}


DbTable::$structure = DbStructure::get();