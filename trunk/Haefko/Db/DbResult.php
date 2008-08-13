<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.8
 * @package     Haefko
 */



/**
 * Trida s rozparsovanym vysledekem dotazu
 */
class DbResult implements Countable, IteratorAggregate
{

	/** @var DbDriver */
	private $driver;

	/** @var array|bool */
	private $widthTables = false;

	/** @var array */
	private $result = array();

	/** @var DbResultNode */
	private $row;

	/** @var DbResultNode */
	private $rows;

	/** @var bool */
	private $fetched = false;

	/** @var array */
	private $assoc;



	/**
	 * Constructor
	 * @param   DbDriver
	 * @param   array  Information about sql query (used tables, etc.)
	 * @return  void
	 */
	public function __construct($driver, $info)
	{
		$this->driver = $driver;

		$this->withTables = count(array_keys($info['tables'])) > 1;
		if (!empty($info['assoc']))
			$this->assoc = $info['assoc'];

		if ($this->withTables) {
			$this->withTables = array();
			$cols = $this->driver->columnsMeta();
			foreach ($cols as $col)
				$this->withTables[] = array($col['table'], $col['name']);
		}
	}



	/**
	 * Return value of first filed of db result
	 * @return  mixed
	 */
	public function fetchField()
	{
		if ($this->fetchRow()) {
			$row = $this->lastRow();
			return $row[0];
		}

		return false;
	}



	/**
	 * Return one row of result with assocciation $assoc
	 * @param   array  Association
	 * @return  DbResultNode
	 */
	public function fetch($assoc = array())
	{
		$result = $parent = false;
		if (empty($assoc))
			$assoc = $this->assoc;

		if (empty($assoc)) {
			if (!$this->fetched && $this->fetchRow())
				return new DbResultNode($this->lastRow());
			else
				return false;
		} else {

			if (!$this->fetched && $this->lastRow() !== false)
				$data = array(end($this->result));
			else
				$data = array();

			while ($this->fetchRow()) {
				$row = $this->lastRow();
				if (!$parent)
					$parent = $row->{$assoc[0]};

				if ($parent != $row->{$assoc[0]})
					break;
				else
					$data[] = $row;
			}

			if (empty($data))
				return false;

			foreach ($data as $i => $row) {
				if (!is_object($row))
					continue;

				foreach (get_object_vars($row) as $name => $node) {
					if ($node instanceof DbResultNode) {
						if ($name !== $assoc[0] && (!isset($assoc[$name]) || (isset($assoc[$name]) && $assoc[$name] != 'hasOne'))) {
							if ($i == 0)
								$result[$name] = array($node);
							else
								$result[$name][] = $node;
						} elseif ($i == 0) {
							$result[$name] = $node;
						}
					} else {
						$result[$name] = $node;
					}
				}
			}

			unset($data);
			return new DbResultNode($result);
		}
	}



	/**
	 * Return array of all (associated) rows
	 * @param   array  Association
	 * @return  array
	 */
	public function fetchAll($assoc = array())
	{
		if (is_null($this->rows)) {

			$this->rows = array();
			while (($row = $this->fetch($assoc)) !== false)
				$this->rows[] = $row;
		}

		return $this->rows;
	}



	/**
	 * Vrati pocet ovlivnenych radku
	 * @return  int
	 */
	public function affectedRows()
	{
		return $this->driver->affectedRows();
	}



	/**
	 * IteratorAggregate interface
	 * @return  ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->fetchAll());
	}



	/**
	 * Countable interface
	 * @return  int
	 */
	public function count()
	{
		return $this->driver->rowCount();
	}



	/**
	 * Return last row of db result
	 * @return  array
	 */
	private function lastRow()
	{
		if (empty($this->result))
			return false;
		else
			return end($this->result);
	}



	/**
	 * Proccess one row of db result
	 * @return  bool
	 */
	private function fetchRow()
	{
		if ($this->withTables === false) {

			$row = $this->driver->fetch(true);
			if (!is_array($row)) {
				$this->fetched = true;
				return false;
			}

			$this->result[] = new DbResultNode($row);
		} else {

			$row = $this->driver->fetch(false);
			if (!is_array($row)) {
				$this->fetched = true;
				return false;
			}

			$node = array();
			foreach ($this->withTables as $i => $item) {
				if (empty($item[0])) {
					$node[$item[1]] = $row[$i];
				} else {
					if (!isset($node[$item[0]]))
						$node[$item[0]] = array();

					$node[$item[0]][$item[1]] = $row[$i];
				}
			}

			foreach ($node as $x => $n) {
				if (is_array($n)) {
					if (implode('', $n) == '')
						$node[$x] = array();
					else
						$node[$x] = new DbResultNode($n);
				}
			}

			$this->result[] = new DbResultNode($node);
		}
		return true;
	}



}


/**
 * Class for db result
 */
class DbResultNode implements ArrayAccess
{

	/** @var array */
	private $keys = array();



	/**
	 * Konstruktor
	 * @param   array   Pole s daty
	 * @return  void
	 */
	public function __construct($data)
	{
		$i = 0;
		foreach ($data as $key => $val) {
			$this->$key = $val;
			$this->keys[$i++] = $key;
		}
	}



	/**
	 * Magic method
	 * @return  void
	 */
	public function __get($name)
	{
		throw new DbResultException("Undefined field '$name'.");
	}



	/**
	 * Array-access
	 * @return  void
	 */
	public function offsetSet($key, $value)
	{
		throw new DbResultException("You can not set the new value to '$key'.");
	}



	/**
	 * Array-access
	 * @return  FormItem
	 */
	public function offsetGet($key)
	{
		if (is_int($key) && isset($this->keys[$key]))
			return $this->{$this->keys[$key]};
		else
			return $this->{$key};
	}



	/**
	 * Array-access
	 * @return  void
	 */
	public function offsetUnset($key)
	{
		throw new DbResultException("You can not unset the '$key'.");
	}



	/**
	 * Array-access
	 * @return  void
	 */
	public function offsetExists($key)
	{
		if (is_int($key))
			return isset($this->keys[$key]);
		else
			return isset($this->{$key});
	}



}