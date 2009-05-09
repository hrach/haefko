<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Application
 * @subpackage  Database
 */


require_once dirname(__FILE__) . '/tools.php';
require_once dirname(__FILE__) . '/db-structure.php';


abstract class DbTable extends Object
{


	/** @var DbStructure */
	public static $structure;

	public static function initTable($name)
	{
		$class = Tools::camelize($name) . 'Table';
		eval("class $class extends DbTable {}");
		return new $class();
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
		return db::fetch("SELECT $cols FROM %c WHERE %c = $mod LIMIT 1", $this->table, $col, $this->primaryKeyValue);
	}


	/**
	 * Returns table form
	 * @throws  Exception
	 * @return  Form
	 */
	public function getForm()
	{
		$form = new Form();
		$cols = self::$structure->getCols($this->table);

		foreach ($cols as $name => $data) {
			if ($data["primary"])
				continue;

			switch ($data['type']) {
			case 'text':
			case 'logntext':
			case 'tinytext':
			case 'mediumtext':
				$form->addTextarea($name);
				$form[$name]->addRule(Form::LENGTH, array(0, $data['length']));
				if (!$data['null'])
					$form[$name]->addRule(Form::FILLED);

				break;

			case 'varchar':
				$form->addText($name);
				$form[$name]->addRule(Form::LENGTH, array(0, $data['length']));
				if (!$data['null'])
					$form[$name]->addRule(Form::FILLED);

				break;

			case 'int':
				$form->addText($name);
				$form[$name]->addRule(Form::INTEGER);
				break;

			case 'float':
				$form->addText($name);
				$form[$name]->addRule(Form::NUMERIC);

			case 'enum':
				$options = array();
				foreach ($data['length'] as $label)
					$options[$label] = ucfirst($label);
				
				$form->addSelect($name, $options);
				break;

			case 'set':
				$options = array();
				foreach ($data['length'] as $label)
					$options[$label] = ucfirst($label);
				
				$form->addMultiCheckbox($name, $options);
				break;

			case 'date':
			case 'datetime':
			case 'time':
			default:
				throw new Exception('Not implemented yet');
			}
		}

		$form->addSubmit('submit');
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
	 * Saves (inserts|updates) db row
	 * @return  mixed     primary key's value
	 */
	public function save()
	{
		$fields = array();
		foreach ($this->fields as $column => $field) {
			if ($field instanceof DbTable)
				$fields[$column . $this->getModificator($column)] = $field->save();
			else
				$fields[$column . $this->getModificator($column)] = $field;
		}

		if (empty($this->primaryKeyValue)) {
			$this->primaryKeyValue = db::query('INSERT INTO %c %kv', $this->table, $fields);

		} else {
			$mod = self::$structure->getModificator($this->table, $this->primaryKey);
			db::query("UPDATE %c SET %l WHERE %c = $mod", $this->table, $fields, $this->primaryKey, $this->primaryKeyValue);
		
		}

		$this->fields = array();
		return $this->primaryKeyValue;
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