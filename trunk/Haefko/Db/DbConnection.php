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
require_once dirname(__FILE__) . '/IDbDriver.php';



/**
 * Class of dv connection
 */
class DbConnection
{

	/** @var bool */
	private $connected = false;

	/** @var mixed */
	private $connection;

	/** @var IDbDriver */
	private $driver;

	/** @var array */
	private $query;



	/**
	 * Constructor
	 * @param   array   connection configuration
	 * @return  void
	 */
	public function __construct(array $config)
	{
		$default = array(
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
	 * @param   mixed  Fragments of sql query
	 * @return  DbResultNode
	 */
	public function query($sql)
	{
		$this->needConnection();

		$args = func_get_args();
		$query = $this->parseSql($args);

		Event::invoke('Db.beforeQuery', array(& $query));
		$query['result'] = new DbResult($this->driver->query($query['sql']), $query);
		Event::invoke('Db.afterQuery', array(& $query));

		return $query['result'];
	}



	public function fetch($sql)
	{
		$args = func_get_args();
		$result = call_user_func_array(array($this, 'query'), $args);
		return $result->fetch($sql);
	}



	public function fetchField($sql)
	{
		$args = func_get_args();
		$result = call_user_func_array(array($this, 'query'), $args);
		return $result->fetchField();
	}



	public function fetchAll($sql)
	{
		$args = func_get_args();
		$result = call_user_func_array(array($this, 'query'), $args);
		return $result->fetchAll();
	}



	private function parseSql($args)
	{
		static $replaceKeys = array("#%if#i", "#%end#i", '#/\*\s*\*/#', "#(?<=from\s|join\s)\[(.+)\]#Uie", "#\[(.+)\]#Ue");
		static $replaceVals = array('/*', '*/', '', '$this->quote("\\1", "table")', '$this->quote("\\1", "column")');

		$skip = false;
		$sql = '';

		for ($i = 0, $c = count($args); $i < $c; $i++) {
			$arg = & $args[$i];
			if ($skip && preg_match('#%end#i', $arg))
				$skip = false;

			if ($skip)
				continue;

			if (preg_match_all('#(%(?:i|s|b|c)(?!f))#i', $arg, $match)) {
				array_shift($match);
				foreach ($match as $m)
					$arg = $this->replace($m[0], $args[++$i], $arg);
			}

			if (preg_match('#(%if\s*)$#i', $args[$i])) {
				if (!(bool) $args[++$i])
					$skip = true;
			}

			$sql .= $arg;
		}

		$query = array(
			'sql' => '',
			'assoc' => '',
			'tables' => array()
		);

		if (preg_match('#select\s+\[(.+)\..+\](?:\s*,\s*\[(.+)\..+\])*#i', $sql, $m) && array_shift($m))
			$query['tables'] = $m;

		$query['sql'] = preg_replace($replaceKeys, $replaceVals, $sql);
		return $query;
	}



	private function replace($key, $value, $string)
	{
		if ($key == '%s')
			$value = $this->driver->quote($this->driver->escape($value), 'value');
		elseif ($key == '%i')
			$value = (integer) $value;
		elseif ($kye == '%b')
			$value == $value ? 1 : 0;
		elseif ($key == '%c')
			$value = $this->driver->quote($value, 'column');

		$pos = stripos($string, $key);
		return substr($string, 0, $pos) . " $value " . substr($string, $pos + strlen($key));
	}



	private function quote($string, $type)
	{
		$e = explode('.', $string);

		if (count($e) == 2)
			return $this->driver->quote($e[0], 'table') . "." . ($e[1] == '*' ? '*' : $this->driver->quote($e[1], 'column'));
		elseif ($string !== '*')
			return $this->driver->quote($string, $type);
		else
			return $string;
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