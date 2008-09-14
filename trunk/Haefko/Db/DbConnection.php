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


require_once dirname(__FILE__) . '/../Event.php';
require_once dirname(__FILE__) . '/Exceptions.php';
require_once dirname(__FILE__) . '/DbResult.php';
require_once dirname(__FILE__) . '/DbResultNode.php';
require_once dirname(__FILE__) . '/IDbDriver.php';


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
	 * Run sql query
	 * @param   mixed  sql query
	 * @return  DbResultNode
	 */
	public function query($sql)
	{
		$this->needConnection();
		$sqls = func_get_args();
		return new DbResult($this->driver->query($this->factorySql($sqls)));
	}


	/**
	 * Transform sql fragment and extern variables to regular sql query
	 * @param   array   sql fragments + variables
	 * @return  string
	 */
	private function factorySql($args)
	{
		$sql = '';
		for ($i = 0, $c = count($args); $i < $c; $i++) {
			$arg = & $args[$i];

			if (is_string($arg)) {
				$arg = preg_replace("#\[(.+)\]#Ue", '$this->quote("\\1")', $arg);

				if (preg_match('#%if#i', $arg)) {
					$sql .= $arg;
					if ((boolean) $args[++$i])
						$sql .= ' */';

					continue;
				}


				if (preg_match_all('#(%(?:s|i|f|b|a|l|v))#i', $arg, $matches)) {
					array_shift($matches);
					foreach ($matches as $match) {
						$pos = strpos($arg, $match[0]);
						$arg = substr($arg, 0, $pos)
							 . $this->sanitize($match[0], $args[++$i])
							 . substr($arg, $pos + 2);
					}
				}

			}

			$sql .= $arg;
		}

		$sql = preg_replace(array('#%if#i', '#%end#'), array('/*', '/* */'), $sql);
		return $sql;
	}



	private function sanitize($type, $value)
	{
		switch ($type) {
		case '%s':  return $this->driver->escape($value, 'text');
		case '%i':  return (integer) $value;
		case '%f':  return (float) $value;
		case '%b':  return (boolean) $value;
		case '%a':  foreach ($this->sanitizeArray($value) as $key => $val)
						$r[] = "$key = $val";
					return implode(', ', $r);
		case '%l':  return "(" . implode(', ', $this->sanitizeArray($value)) . ")";
		case '%v':  return "(" . implode(', ', array_keys($this->sanitizeArray($value))) . ")"
						   . " VALUES (" . implode(', ', $this->sanitizeArray($value)) . ")";
		default:    throw new DbException("Unknow sql query modifier '$type'.");
		}

	}


	private function sanitizeArray(array $array)
	{
		foreach ($array as $key => & $val) {
			$pos = strpos($key, '%');
			if ($pos !== false) {
				$val = $this->sanitize(substr($key, $pos), $val);
				$key = substr($key, 0, $pos);
			}

			if (is_string($val))
				$val = $this->driver->escape($val, 'text');
			$key = $this->driver->escape($key, 'identifier');
		}

		return $array;
	}

	private function quote($string)
	{
		if (strpos($string, '.') !== false) {
			list($table, $field) = explode('.', $string);
			if ($field == '*')
				return $this->driver->escape($table, 'identifier') . '.*';
			else
				return $this->driver->escape($table, 'identifier') . '.' . $this->driver->escape($field, 'identifier');
		} else {
			return $this->driver->escape($string, 'identifier');
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