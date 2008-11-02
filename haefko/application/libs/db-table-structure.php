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


class DbTableStructure
{


	/** @var bool */
	protected $updated = false;

	/** @var array */
	protected $structure = array();


	/**
	 * Returns instance
	 * @return  DbTableStructure
	 */
	public static function i()
	{
		static $instance = null;
		if ($instance === null)
			$instance = new DbTableStructure();

		return $instance;
	}


	/**
	 * Autoload handler for objects of db tables
	 * @param   string    class name
	 * @return  void
	 */
	public function autoloadHandler($class)
	{
		$class = Tools::underscore($class);
		if (in_array($class, $this->structure['__tablesList__']))
			eval ("class $class extends DbTable {} $class::\$table = '$class';");
	}


	/**
	 * Returns column's type
	 * @param   string  table name
	 * @param   string  column name
	 * @return  string
	 */
	public function getType($table, $column)
	{
		if (!isset($this->structure[$table]))
			$this->fetchTable($table);

		if (!isset($this->structure[$table][$column]))
			return 'default';

		return $this->structure[$table][$column]['type'];
	}


	/**
	 * Returns table's primary key's column
	 * @param   string   table name
	 * @throws  Exception
	 * @return  string   column name
	 */
	public function getPk($table)
	{
		if (!isset($this->structure[$table]))
			$this->fetchTable($table);

		foreach ($this->structure[$table] as $name => $column) {
			if ($column['primary'])
				return $name;
		}

		throw new Exception("No primary key in table '$table'.");
	}


	/**
	 * Constructor
	 * @return  void
	 */
	private function __construct()
	{
		$this->structure = Cache::get('sql', 'tables');

		if (!isset($this->structure['__tablesList__'])) {
			$this->structure['__tablesList__'] = db::query('show tables')->fetchPairs();
			$this->updated = true;
		}

		spl_autoload_register(array($this, 'autoloadHandler'));
	}


	/**
	 * Desturctor
	 * @return  void
	 */
	public function __destruct()
	{
		if ($this->updated)
			Cache::put('sql', 'tables', $this->structure, 10);
	}


	/**
	 * Fetchs table structure
	 * @param   string  table name
	 * @return  void
	 */
	private function fetchTable($table)
	{
		$structure = db::query('DESCRIBE ' . $table)->fetchAll();

		foreach ($structure as $row) {
			$this->structure[$table][$row->Field] = array();

			$type = $row->Type;
			$length = null;
			if (preg_match('#^(.*)\((\d+)\)$#', $row->Type, $match)) {
				$type = $match[1];
				$length = $match[2];
			}

			$this->structure[$table][$row->Field]['type'] = $type;
			$this->structure[$table][$row->Field]['length'] = $length;
			$this->structure[$table][$row->Field]['primary'] = $row->Key === 'PRI';
		}

		$this->updated = true;
	}


}


DbTableStructure::i();