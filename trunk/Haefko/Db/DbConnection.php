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
require_once dirname(__FILE__) . '/DbResult.php';
require_once dirname(__FILE__) . '/IDbDriver.php';



/**
 * Trida spojeni s db
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
	 * Konstruktor
	 * @param   array   konfigurace pripojeni
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
	 * 
	 */
	public function query($sql)
	{
		$this->needConnection();

		$args = func_get_args();
		$this->query = array(
			'tables' => array(),
			'table' => '',
			'assoc' => '',
			'sql' => ''
		);

		$this->query['sql'] = $this->parseSql($args);

		Event::invoke('Db.query', array($this->query));
		$this->query['result'] = new DbResult($this->driver->query($this->query['sql']), $this->query);
		Event::invoke('Db.afterQuery', array($this->query));

		return $this->query['result'];
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

			if (preg_match_all('#(%(?:i|s)(?!f))#i', $arg, $match)) {
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

		if (preg_match('#from\s+(?:\[(\w+)\]|(\w+))#i', $sql, $m))
			$this->query['table'] = $m[1];

		if (preg_match('#select\s+\[(.+)\..+\](?:\s*,\s*\[(.+)\..+\])*#i', $sql, $m) && array_shift($m))
			$this->query['tables'] = $m;

		$sql = preg_replace($replaceKeys, $replaceVals, $sql);
		return $sql;
	}



	private function replace($key, $value, $string)
	{
		if ($key == '%s')
			$value = $this->driver->quote($this->driver->escape($value), 'value');
		elseif ($key == '%i')
			$value = (integer) $value;
		elseif ($kye == '%b')
			$value == $value ? 1 : 0;

		$pos = stripos($string, $key);
		return substr($string, 0, $pos) . " $value " . substr($string, $pos + strlen($key));
	}



	private function quote($string, $type)
	{
		if (count(($e = explode('.', $string))) == 2)
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
			else
				throw new Exception("Missing driver $file.");

			$class = "Db{$this->config['driver']}Driver";
			if (!class_exists($class, false))
				throw new Exception("Missing class $class.");

			$this->driver = new $class;
			$this->driver->connect($this->config);
			$this->connected = true;
		}
	}



}