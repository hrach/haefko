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


require_once dirname(__FILE__) . '/table-structure.php';


class Sql
{


	/** @var TableStructure */
	public static $structure;

	/** @var array */
	public static $modificators = array(
		'default' => '%s',
		'varchar' => '%s',
		'char' => '%s',
		'datetime' => '%d',
		'int' => '%i',
		'bigint' => '%i',
		'smallint' => '%i',
	);

	/** @var string */
	protected $table;

	/** @var array */
	protected $fields = array();


	/**
	 * Constructor
	 * @param   string  table name
	 * @return  void
	 */
	public function __construct($table = null)
	{
		if (!empty($table))
			$this->table = $table;

		if (empty($this->table))
			throw new BadMethodCallException('Undefined table name.');

		if (method_exists($this, 'init'))
			$this->init();
	}


	/**
	 * Set the field for condition or insertion
	 * @param   string  column name
	 * @param   mixed   value
	 * @return  Sql     $this
	 */
	public function set($key, $val)
	{
		$this->fields[$key] = $val;
		return $this;
	}


	/**
	 * Magic method
	 */
	public function __set($key, $val)
	{
		$this->set($key, $val);
	}


	/**
	 * Magic method
	 */
	public function __get($key)
	{
		return $this->fields[$key][0];
	}


	/**
	 * Escape value for the key
	 * @param   string  key name
	 * @param   string  value for escape
	 * @return  string
	 */
	protected function escape($key, $val)
	{
		if (strpos($key, '%') !== false) {
			$mod = substr($key, strpos($key, '%'));
		} else {
			$parts = explode('.', $key);
			if (count($parts) > 1)
				$mod = self::$modificators[self::$structure->getType($parts[0], $parts[1])];
			else
				$mod = self::$modificators[self::$structure->getType($this->table, $parts[0])];
		}

		return db::getConnection()->escape($val, $mod);
	}


}


Sql::$structure = new TableStructure();