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


require_once dirname(__FILE__) . '/result.php';
require_once dirname(__FILE__) . '/result-node.php';
require_once dirname(__FILE__) . '/idriver.php';


class DbConnection
{


	/** @var bool */
	private $connected = false;

	/** @var mixed */
	private $connection;

	/** @var IDbDriver */
	private $driver;


	/**
	 * Constructor
	 * @param   array   connection configuration
	 * @return  void
	 */
	public function __construct(array $config)
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

		$this->config = array_merge($default, $config);

		if (!$config['lazy'])
			$this->needConnection();
	}


	/**
	 * Parse and run sql query
	 * @param   mixed  sql query
	 * @return  DbResult
	 */
	public function query($sql)
	{
		$sqls = func_get_args();
		return $this->rawQuery($this->factorySql($sqls));
	}


	/**
	 * Run sql query
	 * @param   string  sql query
	 * @return  DbResult
	 */
	public function rawQuery($sql)
	{
		$this->needConnection();
		return new DbResult($this->driver->query($sql));
	}


	/**
	 * Parse sql query
	 * @param   mixed  sql query
	 * @return  string
	 */
	public function test($sql)
	{
		$sqls = func_get_args();
		$sql = $this->factorySql($sqls);

		static $keys1 = 'SELECT|UPDATE|INSERT(?:\s+INTO)?|REPLACE(?:\s+INTO)?|DELETE|FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|LIMIT|SET|VALUES|LEFT\s+JOIN|INNER\s+JOIN|TRUNCATE';
		static $keys2 = 'ALL|DISTINCT|DISTINCTROW|AS|USING|ON|AND|OR|IN|IS|NOT|NULL|LIKE|TRUE|FALSE';

		// insert new lines
		$sql = ' ' . $sql;
		$sql = preg_replace("#(?<=[\\s,(])($keys1)(?=[\\s,)])#i", "\n\$1", $sql);

		// reduce spaces
		$sql = preg_replace('#[ \t]{2,}#', " ", $sql);

		$sql = wordwrap($sql, 100);
		$sql = htmlSpecialChars($sql);
		$sql = preg_replace("#\n{2,}#", "\n", $sql);

		// syntax highlight
		$sql = preg_replace_callback("#(?<=[\\s,(])($keys1)(?=[\\s,)])|(?<=[\\s,(=])($keys2)(?=[\\s,)=])#is", array($this, 'highlightCallback'), $sql);
		$sql = trim($sql);
		echo '<pre class="dump">'. $sql. "</pre>\n";
	}

	private static function highlightCallback($matches)
	{
		if (!empty($matches[1])) // most important keywords
			return '<strong style="color:blue">' . $matches[1] . '</strong>';

		if (!empty($matches[2])) // other keywords
			return '<strong style="color:green">' . $matches[2] . '</strong>';
	}

	/**
	 * Wrapper for DbResult::fetchField()
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
	 * Transform sql fragment and extern variables to regular sql query
	 * @param   array   sql fragments + variables
	 * @return  string
	 */
	public function factorySql($args)
	{
		$this->needConnection();

		$sql = array_shift($args);
		$sql = preg_replace("#\[(.+)\]#Ue", '$this->escapeColumn("\\1")', $sql);
		$start = 0;
		$nSql = '';

		if (preg_match_all('#(%(?:r|c|s|i|f|b|a|l|v))#', $sql, $matches, PREG_OFFSET_CAPTURE + PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				if (count($args) === 0)
					throw new DbException('Missing sql argumenty.');

				$var = $this->escape(array_shift($args), $match[0][0]);
				$nSql .= substr($sql, $start, $match[0][1] - $start) . $var;
				$start = $match[0][1] + 2;
			}
		} else {
			return $sql;
		}

		return $nSql;
	}



	/**
	 * Escape $value as type $type
	 * @param   string  type of variable
	 * @param   mixed   value
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
				return $this->escapeColumn('column', $value);
			case '%s': # string
				return $this->driver->escape('text', $value);
			case '%i': # integer
				return (integer) $value;
			case '%f': # float
				return (float) $value;
			case '%b': # boolean
				return (boolean) $value;
			case '%a': # array - form: key = val
				foreach ($this->escapeArray($value) as $key => $val)
					$r[] = "$key = $val";
				return implode(', ', $r);
			case '%l': # array list - form: (val1, val2)
				return "(" . implode(', ', $this->escapeArray($value)) . ")";
			case '%v': # array values - form: (key1, key2) VALUES (val1, val2)
				return "(" . implode(', ', array_keys($this->escapeArray($value))) . ')'
				     . " VALUES (" . implode(', ', $this->escapeArray($value)) . ')';
			default:
				throw new DbException("Unknow sql query modifier '$type'.");
		}
	}


	/**
	 * Escape array $array
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
	 * Retrun modificator by varibale type
	 * @param   mixed  variable
	 * @return  string
	 */
	public function getType($var)
	{
		static $types = array(
			'string' => '%s',
			'integer' => '%i',
			'double' => '%f',
			'array' => '%a',
			'boolean' => '%b'
		);

		if (isset($types[gettype($var)]))
			return $types[gettype($var)];
		else
			return '%s';
	}


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


	private function needConnection()
	{
		if (!$this->connected) {
			$file = dirname(__FILE__) . '/drivers/' . strtolower($this->config['driver']) . '.php';
			if (file_exists($file))
				require_once $file;

			$class = "Db{$this->config['driver']}Driver";
			if (!class_exists($class))
				throw new DbException("Missing driver class $class.");

			$this->driver = new $class;
			$this->driver->connect($this->config);
			$this->connected = true;
		}
	}



}