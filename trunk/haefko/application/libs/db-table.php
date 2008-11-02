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
 * 
 * @subpackage Database
 */
abstract class DbTable extends Object
{


	/** @var string */
	public static $table;

	/** @var bool */
	public static $initialized = false;

	/** @var array */
	public static $modificators = array(
		'default' => '%s',
		'varchar' => '%s',
		'char' => '%s',
		'text' => '%s',
		'longtext' => '%s',
		'date' => '%d',
		'datetime' => '%t',
		'time' => '%t',
		'int' => '%i',
		'bigint' => '%i',
		'smallint' => '%i',
	);

	/** @var string */
	private static $pkCol;

	/** @var string */
	private static $pkMod = '%i';

	/** @var mixed */
	private $pkValue;

	/** @var array */
	private $fields = array();

	/** @var array */
	private $fieldMod = array();


	/**
	 * Initializes defaults values
	 * @param   string    primary key column name
	 * @param   string    primary key column modificator
	 * @return  void
	 */
	public static function initialize($pkCol = null, $pkMod = null)
	{
		if (empty(self::$table))
			throw new Exception('Undefined table name.');

		if (!empty($pkCol))
			self::$pkCol = $pkCol;
		else
			self::$pkCol = DbTableStructure::i()->getPk(self::$table);

		$type = DbTableStructure::i()->getType(self::$table, self::$pkCol);
		if ($type == 'default')
			self::$pkMod = '%i';
		else
			self::$pkMod = self::$modificators[$type];

		self::$initialized = true;
	}


	/**
	 * Selects (columns $cols of) table's row by $pk value
	 * @param   mixed        primary key value
	 * @param   strin|array  selected columns 
	 * @return  DbResultNode
	 */
	public static function get($pk, $cols = '*')
	{
		if (!self::$initialized)
			self::initialize();

		return db::query('select ' . (is_array($cols) ? implode(', ', $cols) : $cols)
		               . ' from [' . self::$table . '] where %c = ' . self::$pkMod, self::$pkCol, $pk)->fetch();
	}


	/**
	 * Constructor
	 * @param   string    primary key value
	 * @param   string    primary key column name
	 * @param   string    primary key column modificator
	 * @return  void
	 */
	public function __construct($pkValue = null, $pkCol = null, $pkMod = nuùù)
	{
		$this->pkValue = $pkValue;
		self::initialize($pkCol, $pkMod);
	}


	/**
	 * Magic caller to sets the column value
	 * @param   string  method name
	 * @param   array   array of arguments
	 * @throws  BadMethodCallException
	 * @return  mixed
	 */
	public function __call($method, $args)
	{
		# setter
		if (Tools::startWith($method, 'set')) {
			$column = Tools::underscore(str_replace('_', '.', Tools::lTrim($method, 'set')));

			if ($column == $this->pkCol)
				$this->setPk(array_shift($args));
			else {
				if (strpos($column, '.') === false)
					$column = "{$this->table}.$column";

				$this->setField($column, array_shift($args));
			}

			$this->fieldMod[$column] = array_shift($args);
			return $this;
		}
		
		throw new BadMethodCallException("Undefined method '$method'.");
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

		if (!empty($this->pkCol))
		# update
			db::query("INSERT INTO [{$this->table}] VALUES %v", $fields);
		else
		# insert
			$this->pkCol = db::query("UPDATE [{$this->table}] SET %a WHERE %c = {$this->pkMod}", $fields, $this->pkCol, $this->pkValue);

		$this->fields = array();
		return $this->pkCol;
	}


	/**
	 * Adds escaping modificator to column's name
	 * @param   string    column name
	 * @return  string
	 */
	private function getMod($column)
	{
		if (isset($this->fieldMod[$column][0]))
			return $this->fieldMod[$column][0];

		$parts = explode('.', $column);
		if (count($parts) > 1)
			$type = DbTableStructure::i()->getType($parts[0], $parts[1]);
		else
			$type = DbTableStructure::i()->getType($this->table, $parts[0]);

		return self::$modificators[$type];
	}


	/**
	 * Sets primary key
	 * @param   mixed
	 * @return  void
	 */
	private function setPk($value)
	{
		$this->pkValue = $value;
	}


	/**
	 * Sets $column value
	 * @param   string    column name
	 * @param   mixed     column value
	 * @return  void
	 */
	private function setField($column, $value)
	{
		$this->fields[$column] = $value;
	}


}