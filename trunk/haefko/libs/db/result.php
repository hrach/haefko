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


class DbResult implements Countable, IteratorAggregate
{


	/** @var DbDriver */
	private $driver;

	/** @var array */
	private $cols = array();

	/** @var bool */
	private $tables = false;

	/** @var null|array Stored rows */
	private $rows;

	/** @var null|array Stored row */
	private $stored;


	/**
	 * Constructor
	 * @param   DbDriver
	 * @return  void
	 */
	public function __construct($driver)
	{
		$this->driver = $driver;

		$tables = array();
		foreach ($this->driver->columnsMeta() as $col) {
			$this->cols[] = array($col['table'], $col['name']);
			$tables[$col['table']] = true;
		}

		$this->tables = $tables = count($tables) > 1;
	}


	/**
	 * Return value of the first field
	 * @return  mixed
	 */
	public function fetchField()
	{
		if (is_null($this->rows))
			$this->rows = array($this->fetch());

		if (isset($this->rows[0][0]))
			return $this->rows[0][0];
		else
			return null; // TODO throw Exception
	}


	public function fetchPairs()
	{
		$array = array();
		foreach ($this->fetchAll() as $row) {
			if (!isset($row[1]))
				$array[] = $row[0];
			else
				$array[$row[0]] = $row[1];
		}
		return $array;
	}


	/**
	 * Return one row of result with assocciation $assoc
	 * @param   array         Association
	 * @return  DbResultNode
	 */
	public function fetch($assoc = null)
	{
		$assoc = func_get_args();
		$row = $this->getRow(!$this->tables);
			if (is_null($row))
				return null;

		if ($this->tables) {

			$row = $this->combineColumns($row);

			# association
			if (!empty($assoc)) {
				while (($newRow = $this->getRow(false)) !== null) {
					$this->stored = $newRow;
					$newRow = $this->combineColumns($newRow);

					if (strpos($assoc[0], '.') !== false) {
					# compare table and column
						list($t, $c) = explode('.', $assoc[0]);
						if ($row[$t][$c] != $newRow[$t][$c])
							break;

						$this->stored = null;
						unset($newRow[$t]);
					} else { 
					# compare table
						if ($row[$assoc[0]] != $newRow[$assoc[0]])
							break;

						$this->stored = null;
						unset($newRow[$assoc[0]]);
					}

					# copy tables
					foreach ($newRow as $table => $data) {
						if (!in_array($table, (array) $assoc[1])) {
						# hasOne
							if (!isset($row[$table]))
								$row[$table] = $data;
						} else {
						# hasMany
							if(!is_array($row[$table]))
								$row[$table] = array();

							$row[$table][] = $data;
						}
					}
				}
			}

		}

		return new DbResultNode($row);
	}


	/*
	 * Combine tables with columns and values
	 * @param   array   table row
	 * @return  array
	 */
	private function combineColumns($row)
	{
		$i = 0;
		foreach ($this->cols as $col) {
			if (empty($col[0]))
				$r[$col[1]] = $row[$i++];
			else
				$r[$col[0]][$col[1]] = $row[$i++];
		}

		foreach ($r as & $data) {
			if (is_array($data))
				$data = new DbResultNode($data);
		}

		return $r;
	}


	/**
	 * Return array of all (associated) rows
	 * @param   array  Association
	 * @return  array
	 */
	public function fetchAll($assoc = null)
	{
		while (($row = $this->fetch($assoc)) !== null)
			$this->rows[] = $row;

		return $this->rows;
	}


	/**
	 * Retrun number of affected rows
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
	 * Has result any rows?
	 * @return  bool
	 */
	public function has()
	{
		return $this->count() > 0;
	}


	/**
	 * Proccess one row of db result
	 * @return  bool
	 */
	private function getRow($assoc)
	{
		if (!empty($this->stored)) {
			$stored = $this->stored;
			$this->stored = null;
			return $stored;
		} else {
			return $this->driver->fetch($assoc);
		}
	}


	public function dump()
	{
		$r = '<table>';
		foreach ($this->fetchAll() as $row) {
			$r .= '<tr>';
			foreach ($row as $field) {
				if (is_object($field))
					$r .= '<td>' . print_r($field, true) . '</td>';
						
				else
					$r .= '<td>' . $field . '</td>';
			}
			$r .= '</tr>';
		}
		$r .= '</table>';
		return $r;
	}


}