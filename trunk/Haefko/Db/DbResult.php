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
	 * Konstruktor
	 * @param   DbDriver
	 * @param   array
	 * @return  void
	 */
	public function __construct($driver, $config)
	{
		$this->driver = $driver;
		$this->withTables = count(array_keys($config['tables'])) > 1;
		if ($this->withTables && isset($config['table']))
			$this->assoc = $config['assoc'];

		if ($this->withTables) {
			$this->withTables = array();
			$cols = $this->driver->getColumnsMeta();
			foreach ($cols as $col)
				$this->withTables[] = array($col['table'], $col['name']);
		}
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
	 * Vrati hodnotu prvniho sloupce zaznamu
	 * @return  mixed
	 */
	final function fetchField()
	{
		if ($this->fetchRow()) {
			$row = $this->lastRow();
			return $row[0];
		}

		return false;
	}


	/**
	 * Vrati pole se vsemi zaznami
	 * @return  array
	 */
	public function fetchAll()
	{
		if (is_null($this->rows)) {
			$this->rows = array();
			while (($row = $this->fetch()) !== false)
				$this->rows[] = $row;
		}

		return $this->rows;
	}



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
						if ($name !== $assoc[0] && (!isset($assoc[$name]) || isset($assoc[$name]) && $assoc[$name] != 'hasOne')) {
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
	 * Magic method
	 * @return  mixed
	 */
	public function __get($name)
	{
		if (empty($this->row))
			$this->row = $this->fetch();

		if (isset($this->row->$name))
			return $this->row->$name;
		else
			throw new Exception("Undefined field $name");
	}



	/**
	 * Magic method
	 * @return  mixed
	 */
	public function __isset($name)
	{
		if (empty($this->row))
			$this->row = $this->fetch();

		return isset($this->row->$name);
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
	 * Countable interface.
	 * @return  int
	 */
	public function count()
	{
		return $this->driver->rowCount();
	}



	/**
	 * Vrati posledni radek zpracovaneho dotazu
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
	 * Zpracuje jeden radek z dataze
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
	 * @throw	Exception
	 * @return  void
	 */
	public function __get($name)
	{
		throw new Exception("Undefined field $name");
	}



	/**
	 * Array-access
	 * @return  void
	 */
	public function offsetSet($key, $value)
	{
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
	}



	/**
	 * Array-access
	 * @return  void
	 */
	public function offsetExists($key)
	{
		//return isset($this->form['elements'][$key]);
	}




}