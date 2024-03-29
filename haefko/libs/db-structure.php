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


require_once dirname(__FILE__) . '/cache.php';


class DbStructure
{

	/** @var array */
	public static $modificators = array(
		'varchar' => Db::TEXT,
		'char' => Db::TEXT,
		'bpchar' => Db::TEXT,
		'tinytext' => Db::TEXT,
		'text' => Db::TEXT,
		'mediumtext' => Db::TEXT,
		'longtext' => Db::TEXT,
		'bool' => Db::BOOL,
		'enum' => Db::TEXT,
		'set' => Db::SET,
		'date' => Db::DATE,
		'time' => Db::TIME,
		'datetime' => Db::DATETIME,
		'timestamp' => Db::DATETIME,
		'tinyint' => Db::INTEGER,
		'int' => Db::INTEGER,
		'mediumint' => Db::INTEGER,
		'bigint' => Db::INTEGER,
		'smallint' => Db::INTEGER,
		'float' => Db::FLOAT,
		'double' => Db::FLOAT,
		'decimal' => Db::FLOAT,
	);

	/** @var DbTableStructure */
	protected static $self;

	/** @var bool */
	public $updated = false;

	/** @var array */
	public $structure = array();


	/**
	 * Returns instance
	 * @return DbTableStructure
	 */
	public static function get()
	{
		if (empty(self::$self))
			self::$self = new DbStructure();

		return self::$self;
	}


	/**
	 * Constructor - loads tables cache
	 * @return DbTableStructure
	 */
	private function __construct()
	{
		self::$self = & $this;
		if (class_exists('Application', false))
			$this->cache = Application::get()->cache;
		else
			$this->cache = new Cache();

		$this->structure = $this->cache->get('db_structure');
		if (!isset($this->structure['__tables'])) {
			$this->structure['__tables'] = db::getDriver()->getTables();
			$this->updated = true;
		}
	}


	/**
	 * Desctuctor - save tables cache
	 */
	public function __destruct()
	{
		if ($this->updated)
			$this->cache->set('db_structure', $this->structure, array(
				'expirea' => time() + 60*30
			));
	}


	/**
	 * Returns column's modificator
	 * @param string $table table name (or expression "table.column")
	 * @param string $columne
	 * @return string
	 */
	public function getModificator($table, $column = null)
	{
		if (empty($column))
			list($table, $column) = explode('.', $table);

		$this->initTable($table);
		if (empty($this->structure[$table][$column]))
			throw new Exception("Unknow column '$table.$column'.");

		return '%' . $this->structure[$table][$column]['mod'];
	}


	/**
	 * Returns name of table's primary key
	 * @param string $table table name
	 * @throws Exception
	 * @return string
	 */
	public function getPrimaryKey($table)
	{
		$this->initTable($table);
		foreach ($this->structure[$table] as $name => $data) {
			if ($data['primary'])
				return $name;
		}

		throw new Exception("DbTable is only for tables with primary key. Table '$table' doesn't contain any primary key.");
	}


	/**
	 * Gets list of table cols and modificators
	 * @param string $table table name
	 * @return array
	 */
	public function getCols($table)
	{
		$this->initTable($table);
		return $this->structure[$table];
	}


	/**
	 * Checks if table exists
	 * @param string $table table name
	 * @return bool
	 */
	public function tableExists($table)
	{
		return in_array(Tools::underscore($table), $this->structure['__tables']);
	}


	/**
	 * Fetchs table structure
	 * @param string $table table name
	 * @return void
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