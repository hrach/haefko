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


class DbTableStructure
{


	/** @var array */
	public static $modificators = array(
		'varchar' => '%s',
		'char' => '%s',
		'text' => '%s',
		'longtext' => '%s',
		'date' => '%d',
		'time' => '%t',
		'datetime' => '%dt',
		'timestamp' => '%dt',
		'int' => '%i',
		'bigint' => '%i',
		'smallint' => '%i',
	);

	/** @var DbTableStructure */
	protected static $self;

	/** @var bool */
	public $updated = false;

	/** @var array */
	public $structure = array();


	/**
	 * Returns instance
	 * @param DbTableStructure
	 */
	public static function get()
	{
		if (empty(self::$self))
			self::$self = new DbTableStructure();

		return self::$self;
	}


	/**
	 * Constructor
	 * @return  void
	 */
	public function __construct()
	{
		self::$self = & $this;
		if (class_exists('Application', false))
			$this->cache = Application::get()->cache;
		else
			$this->cache = new Cache();

		$this->structure = $this->cache->read('sql', 'tables');
		if (!isset($this->structure['__tables__'])) {
			$this->structure['__tables__'] = db::getDriver()->getTables();
			$this->updated = true;
		}
	}


	/**
	 * Desctuctor
	 * @return  void
	 */
	public function __destruct()
	{
		if ($this->updated)
			$this->cache->write('sql', 'tables', $this->structure);
	}


	/**
	 * Returns column's modificator
	 * @param   string     table
	 * @param   string     column
	 * @return  string
	 */
	public function getMod($table, $column = null)
	{
		if (empty($column))
			list($table, $column) = explode('.', $table);

		$this->initTable($table);
		if (empty($this->structure[$table][$column]))
			throw new Exception("Unknow column '$table.$column'.");

		return $this->structure[$table][$column]['mod'];
	}


	/**
	 * Returns table primary keys with modificators
	 * @param   string      table name
	 * @return  array|false
	 */
	public function getPk($table)
	{
		$this->initTable($table);

		$pk = array();
		foreach ($this->structure[$table] as $name => $data) {
			if ($data['primary'])
				$pk[$name] = $data['mod'];
		}

		if (!empty($pk))
			return $pk;

		return false;
	}


	/**
	 * Checks whether table exists
	 * @param   string    table name
	 * @return  bool
	 */
	public function existTable($table)
	{
		return in_array(Tools::underscore($table), $this->structure['__tables__']);
	}


	/**
	 * Fetchs table structure
	 * @param   string  table name
	 * @return  void
	 */
	protected function initTable($table)
	{
		if (!empty($this->structure[$table]))
			return;

		$this->structure[$table] = db::getDriver()->getTableColumnsDescription($table);
		foreach ($this->structure[$table] as & $row)
			$row['mod'] = self::$modificators[$row['type']];

		$this->updated = true;
	}


}