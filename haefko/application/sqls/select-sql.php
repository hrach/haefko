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


class SelectSql extends Sql
{


	/** @var string */
	protected $sqlSelect = '*';

	/** @var string */
	protected $sqlJoins;

	/** @var array */
	protected $sqlWhere = array();

	/** @var string */
	protected $sqlGroup;

	/** @var string */
	protected $sqlHaving;

	/** @var string */
	protected $sqlOrder;

	/** @var int */
	protected $sqlLimit;

	/** @var int */
	protected $sqlOffset;


	/** @var bool|array */
	protected $pagination = false;


	/**
	 * Run query
	 * @param   string   column for select
	 * @return  DbResult
	 */
	public function query()
	{
		# select wrapper
		$columns = func_get_args();
		if (!empty($columns))
			call_user_func_array(array($this, 'select'), $columns);

		# factory sql query
		$sql = $this->factorySql();

		# pagination
		if ($this->pagination !== false) {
			$pSql = $sql;
			$sql .= ' LIMIT ' . ($this->pagination[0] - 1) * $this->pagination[1] . ', ' . $this->pagination[1];
		}


		# run query
		$query = db::query($sql);


		# pagination
		if ($this->pagination !== false) {
			$count = db::fetchField(preg_replace('#select (.+) from#si', 'select count(*) from', $pSql));
			$pages = ceil($count / ($this->pagination[0] * $this->pagination[1]));
			$query->paginate = new DbResultNode(array(
				'page' => $this->pagination[0],
				'pages' => $pages,
				'next' => $this->pagination[0] < $pages,
				'prev' => $this->pagination[0] > 1
			));
		}


		return $query;
	}


	public function paginate($page, $limit = 10)
	{
		if ($page === false)
			$this->pagination = false;
		else
			$this->pagination = array($page, $limit);

		return $this;
	}


	public function containable()
	{
		foreach ($this->hasMany as $table => $on) {
			$this->sqlJoins .= " LEFT JOIN [$table] ON $on";
		}

		foreach ($this->hasOne as $table => $on) {
			$this->sqlJoins .= " LEFT JOIN [$table] ON $on";
		}

		return $this;
	}


	public function select($column)
	{
		$columns = func_get_args();
		$this->sqlSelect = implode(', ', $columns);
		return $this;
	}


	/**
	 * Set the fild for condition
	 * @param   string  column name
	 * @param   mixed   value
	 * @param   string  logical conjuction
	 * @param   string  logical operator
	 * @return  Sql     $this
	 */
	public function set($key, $val, $conjuction = 'and', $operator = '=')
	{
		if (!in_array(strtolower($conjuction), array('and', 'or')))
			$conjuction = 'and';

		if (!in_array($operator, array('=', '<=', '>=', '<', '>', '!=')))
			$operator = '=';

		parent::set($key, $val);
		$this->sqlWhere[] = array($conjuction, $operator);

		return $this;
	}


	public function sort($column, $reverse = false)
	{
		$order = "[$column] " . ($reverse ? 'DESC' : 'ASC');

		if (empty($this->sqlOrder))
			$this->sqlOrder  = $order;
		else
			$this->sqlOrder .= ', ' . $order;

		return $this;
	}


	public function group($column)
	{
		$this->sqlGroup = $column;
		return $this;
	}


	public function having($condition)
	{
		$this->sqlHaving = $condition;
		return $this;
	}


	protected function factorySql()
	{
		if (empty($this->sqlSelect))
			$this->sqlSelect = '*';

		# select * from * left join *
		$sql = "SELECT {$this->sqlSelect} FROM {$this->table}{$this->sqlJoins}";

		# where
		if (!empty($this->fields)) {

			$i = 0;
			$where = '';
			$fields = db::getConnection()->escapeArray($this->fields);

			foreach ($fields as $key => $val) {
				list($con, $op) = $this->sqlWhere[$i++];
				if (empty($where))
					$where = "$key $op $val";
				else
					$where .= " $con $key $op $val";
			}

			$sql .= " WHERE $where";
		}

		# group by
		if (!empty($this->sqlGroup))
			$sql .= " GROUP BY {$this->sqlGroup}";

		# having
		if (!empty($this->sqlHaving))
			$sql .= " HAVING {$this->sqlHaving}";

		# order by
		if (!empty($this->sqlOrder))
			$sql .= " ORDER BY {$this->sqlOrder}";

		return $sql;
	}


}