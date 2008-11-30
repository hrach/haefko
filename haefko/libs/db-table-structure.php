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
 *
 * @subpackage Database
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

	/** @var bool */
	public static $updated = false;

	/** @var array */
	public static $structure = array();


	public static function initialize()
	{
		self::$structure = Cache::get('sql', 'tables');

		if (!isset(self::$structure['__tablesList__'])) {
			self::$structure['__tablesList__'] = db::query('show tables')->fetchPairs();
			self::$updated = true;
		}
	}


	/**
	 * Returns column's modificator
	 * @param   string     table
	 * @param   string     column
	 * @return  string
	 */
	public static function getMod($table, $column = null)
	{
		if (empty($column))
			list($table, $column) = explode('.', $table);

		self::initTable($table);
		if (empty(self::$structure[$table][$column]))
			throw new Exception("Unknow column '$table.$column'.");

		return self::$structure[$table][$column]['mod'];
	}


	/**
	 * Returns table primary keys with modificators
	 * @param   string      table name
	 * @return  array|false
	 */
	public function getPk($table)
	{
		self::initTable($table);

		$pk = array();
		foreach (self::$structure[$table] as $name => $data) {
			if ($data['primary'])
				$pk[$name] = $data['mod'];
		}

		if (!empty($pk))
			return $pk;

		return false;
	}


	/**
	 * Fetchs table structure
	 * @param   string  table name
	 * @return  void
	 */
	private function initTable($table)
	{
		if (!empty(self::$structure[$table]))
			return true;

		foreach (db::query("DESCRIBE [$table]")->fetchAll() as $row) {
			self::$structure[$table][$row->Field] = array();

			$type = $row->Type;
			$length = null;
			if (preg_match('#^(.*)\((\d+)\)$#', $row->Type, $match)) {
				$type = $match[1];
				$length = $match[2];
			}

			self::$structure[$table][$row->Field]['mod'] = self::$modificators[$type];
			self::$structure[$table][$row->Field]['length'] = $length;
			self::$structure[$table][$row->Field]['primary'] = $row->Key === 'PRI';
		}

		self::$updated = true;
	}

	public static function existTable($table)
	{
		return in_array(Tools::underscore($table), self::$structure['__tablesList__']);
	}


}


DbTableStructure::initialize();