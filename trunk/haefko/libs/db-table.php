<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
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
	 * @param string $name table name
	 * @return string
	 */
	public static function init($table)
	{
		$class = Tools::camelize($table) . 'Table';
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
	 * @param mixed $primaryKeyValue
	 * @throws Exception
	 * @return DbTable
	 */
	public function __construct($primaryKeyValue = null)
	{
		if (empty($this->table)) {
			$table = $this->getClass();
			$table = strpos($table, 'Table') == strlen($table) - 5 ? substr($table, 0, -5) : $table;
			$table = Tools::underscore($table);
			$this->table = $table;
		}

		if (!self::$structure->tableExists($this->table))
			throw new Exception("Db table \"{$this->table}\" does not exists.");


		$this->primaryKey = self::$structure->getPrimaryKey($this->table);
		$this->primaryKeyValue = $primaryKeyValue;
	}


	/**
	 * Selects $cols from table
	 * @param string|array $cols selected columns
	 * @return DbResultNode
	 */
	public function get($cols = '*')
	{
		$cols = $this->toSqlCols($cols);
		$mod = self::$structure->getModificator($this->table, $this->primaryKey);
		return Db::fetch("SELECT $cols FROM %c WHERE %c = $mod LIMIT 1", $this->table, $this->primaryKey, $this->primaryKeyValue);
	}


	/**
	 * Selects $cols from table by $col
	 * @param string $col column
	 * @param string|array $cols selected columns
	 * @return DbResultNode
	 */
	public function getBy($col, $cols = '*')
	{
		if (!isset($this->fields[$col]))
			throw new Exception("You have to set a value for selecting by column '$col' in method getBy().");

		$cols = $this->toSqlCols($cols);
		$mod = self::$structure->getModificator($this->table, $col);
		$res = Db::fetch("SELECT $cols FROM %c WHERE %c = $mod LIMIT 1", $this->table, $col, $this->fields[$col]);
		if (isset($res[$this->primaryKey]))
			$this->primaryKeyValue = $res[$this->primaryKey];

		return $res;
	}


	/**
	 * Returns table form
	 * @param array $editCols
	 * @param array $labels
	 * @param string $formUrl
	 * @param string @formName
	 * @throws Exception
	 * @return Form
	 */
	public function getForm($editCols = array(), $labels = array(), $formUrl = null, $formName = null)
	{
		$form = new Form($formUrl, $formName);
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
				$form->addText($name, $label);
				if (!$data['null'])
					$form[$name]->addRule(Rule::FILLED);

				break;

			case 'char':
				$form->addText($name, $label);
				if (!$data['null']) {
					$form[$name]->addRule(Rule::FILLED);
					$form[$name]->addRule(Rule::LENGTH, $data['length']);
				} else {
					$form[$name]->addCondition(Rule::FILLED)
					            ->addRule(Rule::LENGTH, $data['length']);
				}

				break;

			case 'varchar':
			default:
				$form->addText($name, $label);
				if (!$data['null'])
					$form[$name]->addRule(Rule::FILLED);
				$form[$name]->addRule(Rule::LENGTH, '<=' . $data['length']);

				break;
			}
		}

		$form->addSubmit('submit', isset($labels['submit']) ? $labels['submit'] : null);
		return $form;
	}


	/**
	 * Sets column value
	 * @param string $column column name
	 * @param mixed $value column value
	 * @param string $mod column modificator
	 * @return DbTable
	 */
	public function set($column, $value, $mod = null)
	{
		if ($this->primaryKey == $column) {
			$this->primaryKeyValue = $value;

		} else {
			if (!empty($mod))
				$this->fieldsModificators[$column] = $mod;

			$this->fields[$column] = $value;
		}

		return $this;
	}


	/**
	 * Imports data from array
	 * @param array $data column => value
	 * @return DbTable
	 */
	public function import($data)
	{
		foreach ((array) $data as $column => $value)
			$this->set($column, $value);

		return $this;
	}


	/**
	 * Saves (inserts|updates) db entry
	 * @return mixed primary key's value
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
			$this->primaryKeyValue = Db::query('INSERT INTO %c %v', $this->table, $fields);
		} else {
			$mod = self::$structure->getModificator($this->table, $this->primaryKey);
			Db::query("UPDATE %c SET %a WHERE %c = $mod", $this->table, $fields, $this->primaryKey, $this->primaryKeyValue);
		}

		$this->fields = array();
		return $this->primaryKeyValue;
	}


	/**
	 * Removes db entry
	 * @throws Exception
	 * @return bool
	 */
	public function remove()
	{
		if (empty($this->primaryKeyValue) && $this->primaryKeyValue !== 0)
			throw new Exception('Primary key have to be set for removing entry.');

		$mod = self::$structure->getModificator($this->table, $this->primaryKey);
		Db::query("DELETE FROM %c WHERE %c = $mod LIMIT 1", $this->table, $this->primaryKey, $this->primaryKeyValue);
		return Db::affectedRows() == 1;
	}


	/**
	 * Call interface
	 * @param string $method
	 * @param array $args
	 * @throws BadMethodCallException
	 * @return DbTable
	 */
	public function __call($method, $args)
	{
		if (!Tools::startWith($method, 'set'))
			throw new BadMethodCallException("Undefined method DbTable::$method().");

		$column = substr($method, 3);
		$column = str_replace('_', '.', $column);
		$column = Tools::underscore($column);
		return $this->set($column, array_shift($args), array_shift($args));
	}


	/**
	 * Returns modificator for column
	 * @param string $column column name
	 * @return string
	 */
	private function getModificator($column)
	{
		if (!empty($this->fieldsModificators[$column]))
			return '%' . $this->fieldsModificators[$column];

		$parts = explode('.', $column);
		if (count($parts) > 1)
			return self::$structure->getModificator($parts[0], $parts[1]);
		else
			return self::$structure->getModificator($this->table, $parts[0]);
	}


	/**
	 * Transforms array of cols as sql string
	 * @param string $cols
	 * @return string
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