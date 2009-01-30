<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Database
 */


require_once dirname(__FILE__) . '/driver.php';
require_once dirname(__FILE__) . '/result.php';


class DbConnection extends Object
{


	/** @var bool */
	private $connected = false;

	/** @var IDbDriver */
	private $driver;

	/** @var array */
	private $config = array();

	/** @var array */
	private $sqls = array();


	/**
	 * Constructor
	 * @param   array   connection configuration
	 * @return  void
	 */
	public function __construct($config)
	{
		static $default = array(
			'driver' => 'mysqli',
			'server' => 'localhost',
			'username' => 'root',
			'password' => '',
			'database' => '',
			'encoding' => 'utf8',
			'lazy' => false
		);

		$this->config = array_merge($default, (array) $config);

		if (!$this->config['lazy'])
			$this->needConnection();
	}


	/**
	 * Returns result object with parsed sql query
	 * @param   string    sql query
	 * @return  DbResult
	 */
	public function prepare($sql)
	{
		$sqls = func_get_args();
		return new DbResult($this->factorySql($sqls), $this->driver);
	}


	/**
	 * Returns result object with executed parsed sql query
	 * or last inserted id
	 * @param   string        sql query
	 * @return  DbResult|int
	 */
	public function query($sql)
	{
		$sqls = func_get_args();
		$sql = $this->factorySql($sqls);

		if (preg_match('#^(select|describe|show|explain)#i', $sql)) {
			$result = new DbResult($sql, $this->driver);
			return $result->execute();
		} else {
			$time = microtime(true);
			$driver = $this->driver->query($sql);
			Db::debug($sql, $time);

			if (stripos($sql, 'insert') === 0 && in_array(get_class($this->driver), array('MysqlDbDriver', 'MysqliDbDriver', 'PdoDbDriver', 'SqliteDbDriver')))
				return $driver->insertedId('');
			else
				return null;
		}
	}


	/**
	 * Wrapper for called result
	 * @see     DbResult::fetchField()
	 * @param   string    sql query
	 * @return  mixed
	 */
	public function fetchField($args)
	{
		$args = func_get_args();
		$query = call_user_func_array(array($this, 'query'), $args);
		return $query->fetchField();


	}
	/**
	 * Wrapper for called result
	 * @see     DbResult::fetch()
	 * @param   string    sql query
	 * @return  mixed
	 */
	public function fetch($args)
	{
		$args = func_get_args();
		$query = call_user_func_array(array($this, 'query'), $args);
		return $query->fetch();
	}


	/**
	 * Wrapper for called result
	 * @see     DbResult::fetchAll()
	 * @param   string    sql query
	 * @return  mixed
	 */
	public function fetchAll($args)
	{
		$args = func_get_args();
		$query = call_user_func_array(array($this, 'query'), $args);
		return $query->fetchAll();
	}


	/**
	 * Wrapper for called result
	 * @see     DbResult::fetchPairs()
	 * @param   string    sql query
	 * @return  array
	 */
	public function fetchPairs($args)
	{
		$args = func_get_args();
		$query = call_user_func_array(array($this, 'query'), $args);
		return $query->fetchPairs();
	}


	/**
	 * Return number of affected rows
	 * @return  int
	 */
	public function affectedRows()
	{
		return $this->driver->affectedRows();
	}


	/**
	 * Transforms sql and variables to regular sql query
	 * @param   array     sql fragments and variables
	 * @return  string
	 */
	public function factorySql($args)
	{
		$this->needConnection();

		$sql = '';
		$cond = array();
		while (($frag = array_shift($args)) !== null) {

			if (is_string($frag))
				$frag = preg_replace("#\[(.+)\]#Ue", '$this->escapeColumn("\\1")', $frag);

			if (is_string($frag) && preg_match_all('#(%(?:r|c|s|i|f|b|d|t|dt|a|l|v|if|end))(?!\w)#', $frag, $matches, PREG_OFFSET_CAPTURE + PREG_SET_ORDER)) {
				$temp = '';
				$start = 0;
				foreach ($matches as $match) {
					$temp .= substr($frag, $start, $match[0][1] - $start);
					$start = $match[0][1] + strlen($match[0][0]);

					if ($match[0][0] == '%if') {
						if (array_shift($args) == false) {
							$temp .= '/* ';
							$cond[] = false;
						} else {
							$cond[] = true;
						}
					} elseif ($match[0][0] == '%end') {
						$pop = array_pop($cond);

						if ($pop == false && !in_array(false, $cond))
							$temp .= '*/';
					} else {
						if (empty($args))
							throw new Exception('Missing sql argument.');
						else
							$arg = $this->escape(array_shift($args), $match[0][0]);

						$temp .= $arg . ' ';
					}
				}

				if (strlen($start) < strlen($frag))
					$temp .= substr($frag, $start);

				$sql .= $temp;
			} else {
				if (is_string($frag))
					$sql .= $frag;
				else
					$sql .= $this->escape($frag);
			}
		}

		return trim($sql);
	}


	/**
	 * Escapes $value as type $type
	 * @param   string    type of variable
	 * @param   mixed     value
	 * @return  mixed
	 */
	public function escape($value, $type = null)
	{
		$this->needConnection();

		if (empty($type))
			$type = $this->getType($value);

		switch ($type) {
			case '%r': # raw sql format
				return $value;
			case '%c': # sql column
				return $this->escapeColumn($value);
			case '%s': # string
				return $this->driver->escape('text', $value);
			case '%i': # integer
				return (integer) $value;
			case '%f': # float
				return (float) $value;
			case '%b': # boolean
				return (boolean) $value;
			case '%t': # time
				return $this->driver->escape('text', date('H:i:s', is_int($value) ? $value : strtotime($value)));
			case '%d': # date
				return $this->driver->escape('text', date('Y-m-d', is_int($value) ? $value : strtotime($value)));
			case '%dt': # datetime
				return $this->driver->escape('text', date('Y-m-d H:i:s', is_int($value) ? $value : strtotime($value)));
			case '%a': # array - form: key = val
				foreach ($this->escapeArray($value) as $key => $val)
					$r[] = "$key = $val";
				return implode(', ', $r);
			case '%l': # array list - form: (val1, val2)
				return "(" . implode(', ', $this->escapeArray($value)) . ")";
			case '%v': # array values - form: (key1, key2) VALUES (val1, val2)
				$array = $this->escapeArray($value);
				return "(" . implode(', ', array_keys($array)) . ')'
				     . " VALUES (" . implode(', ', $array) . ')';
			default:
				throw new Exception("Unknow sql query modifier '$type'.");
		}
	}


	/**
	 * Escapes array
	 * Uses modificators in the key(column%i)
	 * @param   array
	 * @return  array
	 */
	public function escapeArray($array)
	{
		$this->needConnection();

		$eArray = array();
		foreach ((array) $array as $key => $val) {
			$key = explode('%', $key);
			if (count($key) == 1)
				$eArray[$this->escapeColumn($key[0])] = $this->escape($val);
			else
				$eArray[$this->escapeColumn($key[0])] = $this->escape($val, '%' . $key[1]);
		}

		return $eArray;
	}


	/**
	 * Excapes columns
	 * @param   string     column
	 * @return  string
	 */
	private function escapeColumn($i)
	{
		$i = explode('.', $i);
		if (count($i) === 1) {
			return $this->driver->escape('column', $i[0]);
		} else {
			if ($i[1] == '*')
				return $this->driver->escape('column', $i[0]) . '.*';
			else
				return $this->driver->escape('column', $i[0]) . '.' . $this->driver->escape('column', $i[1]);
		}
	}


	/**
	 * Retruns modificator by varibale type
	 * @param   mixed     variable
	 * @return  string
	 */
	public function getType($var)
	{
		switch (gettype($var)) {
			case 'integer':    return '%i';
			case 'double':     return '%f';  # float
			case 'array':      return '%a';
			case 'boolean':    return '%b';
			default:           return '%s';
		}
	}


	/**
	 * Checks connection and creates it
	 * @throws  DbException
	 * @return  void
	 */
	private function needConnection()
	{
		if ($this->connected)
			return;

		$file = dirname(__FILE__) . '/drivers/' . strtolower($this->config['driver']) . '.php';
		if (file_exists($file))
			require_once $file;

		$class = $this->config['driver'] . 'DbDriver';
		if (!class_exists($class))
			throw new Exception("Missing database driver $class.");

		$this->driver = new $class;
		$this->driver->connect($this->config);
		$this->connected = true;
	}


}