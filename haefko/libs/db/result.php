<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko_Database
 */


require_once dirname(__FILE__) . '/result-node.php';


class DbResult extends Object implements Countable, IteratorAggregate
{


	/** @var Paginator */
	public $paginator;

	/** @var string */
	private $query;

	/** @var DbDriver */
	private $driver;

	/** @var bool */
	private $executed = false;

	/** @var array */
	private $cols = array();

	/** @var bool */
	private $tables = false;

	/** @var array Stored rows */
	private $rows = array();

	/** @var null|array Stored row */
	private $stored = array();

	/** @var array */
	private $association = array();

	/** @var boll|array */
	private $pagination = false;


	/**
	 * Constructor
	 * @param   string     sql query
	 * @param   DbDriver   driver instance
	 * @return  void
	 */
	public function __construct($query, DbDriver $driver)
	{
		$this->query = $query;
		$this->driver = $driver;
	}


	/**
	 * Executes sql query
	 * @return DbResult    $this
	 */
	public function execute()
	{
		# pagination
		if ($this->pagination !== false) {
			if (empty($this->pagination[2]))
				$this->pagination[2] = db::fetchField(preg_replace('#select (.+) from#si', 'SELECT COUNT(*) FROM', $this->query));

			$this->query .= ' LIMIT ' . ($this->pagination[0] - 1) * $this->pagination[1] . ', ' . $this->pagination[1];
		}


		# run query
		$time = microtime(true);
		$this->driver = $this->driver->query($this->query);
		$this->executed = true;
		Db::debug($this->query, $time);

		# pagination
		if ($this->pagination !== false) {
			require_once dirname(__FILE__) . '/../paginator.php';
			$this->paginator = new Paginator($this->pagination[0], ceil($this->pagination[2] / $this->pagination[1]));
		}


		# columns & tables
		$tables = array();
		foreach ($this->driver->columnsMeta() as $col) {
			$this->cols[] = array($col['table'], $col['name']);
			$tables[$col['table']] = true;
		}
		$this->tables = count($tables) > 1;

		return $this;
	}


	/**
	 * Sets association
	 * @param   string     main table
	 * @param   array      other tables
	 * @return  DbResult   $this
	 */
	public function associate($main, $hasMany = array())
	{
		$this->association[0] = $main;
		$this->association[1] = (array) $hasMany;

		return $this;
	}


	/**
	 * Sets pagination
	 * @param   int        page
	 * @param   int        limit
	 * @param   int        count pages
	 * @throws  Exception
	 * @return  DbResult   $this
	 */
	public function paginate($page, $limit = 10, $count = null)
	{
		if ($this->executed)
			throw new Exception("You can't paginate excecuted query.");

		if ($page < 1)
			$page = 1;

		$this->pagination[0] = $page;
		$this->pagination[1] = (int) $limit;
		if (!empty($count))
			$this->pagination[2] = (int) $count;

		return $this;
	}


	/**
	 * Returns first field value
	 * @return  mixed
	 */
	public function fetchField()
	{
		$this->checkExecution();

		if (empty($this->rows))
			$this->rows = array($this->fetch());

		if (empty($this->rows[0]))
			throw new Exception('No result');

		return current($this->rows[0]);
	}


	/**
	 * Returns array of pairs
	 * @return array
	 */
	public function fetchPairs()
	{
		$this->checkExecution();

		$array = array();
		foreach ($this->fetchAll() as $row) {
			if (count((array) $row) == 1)
				$array[] = current($row);
			else
				$array[current($row)] = next($row);
		}

		return $array;
	}


	/**
	 * Returns one (associated) row
	 * @return  DbResultNode
	 */
	public function fetch()
	{
		$this->checkExecution();

		$row = $this->getRow(!$this->tables);
		if (is_null($row))
			return null;

		if ($this->tables) {
			$row = $this->combineColumns($row);

			# association
			if (!empty($this->association)) {
				# prepare hasMany
				foreach ($row as $table => $data) {
					if (in_array($table, $this->association[1]))
						$row[$table] = array($data);
				}

				# add associated rows
				while (($newRow = $this->getRow(false)) !== null) {
					$this->stored = $newRow;
					$newRow = $this->combineColumns($newRow);

					if (strpos($this->association[0], '.') !== false) {
					# compare table and column
						list($t, $c) = explode('.', $this->association[0]);
						if ($row[$t][$c] != $newRow[$t][$c])
							break;

						unset($newRow[$t]);
					} else { 
					# compare table
						if (!isset($row[$this->association[0]]) || $row[$this->association[0]] != $newRow[$this->association[0]])
							break;

						unset($newRow[$this->association[0]]);
					}
					$this->stored = null;

					# copy tables
					foreach ($newRow as $table => $data) {
						if (!in_array($table, $this->association[1]))
						# hasOne
							$row[$table] = $data;
						else
						# hasMany
							$row[$table][] = $data;
					}
				}
			}

		}

		return $this->rows[] = new DbResultNode($row);
	}


	/**
	 * Returns all (associated) rows
	 * @return  array
	 */
	public function fetchAll()
	{
		$this->checkExecution();

		while (($row = $this->fetch()) != null);
		return $this->rows;
	}


	/**
	 * Retruns num of affected rows
	 * @return  int
	 */
	public function affectedRows()
	{
		$this->checkExecution();
		return $this->driver->affectedRows();
	}


	/**
	 * IteratorAggregate interface
	 * @return  ArrayIterator
	 */
	public function getIterator()
	{
		$this->checkExecution();
		return new ArrayIterator($this->fetchAll());
	}


	/**
	 * Countable interface
	 * @return  int
	 */
	public function count()
	{
		$this->checkExecution();
		return $this->driver->rowCount();
	}


	/**
	 * Checkes if has been sql query executed and 
	 * @return  void
	*/
	private function checkExecution()
	{
		if (!$this->executed)
			$this->execute();
	}


	/**
	 * Returns one reuslt row
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


	/*
	 * Combines tables with their columns
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


}