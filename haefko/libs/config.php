<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
 */


class Config
{

	/** @var array */
	public static $config = array();


	/**
	 * Writes configuration
	 * @param string $key key name
	 * @param mixed $val
	 * @return void
	 */
	public static function write($key, $val)
	{
		$key = strtolower($key);
		if ($key == 'servers' && is_array($val)) {
			$server = $_SERVER['SERVER_NAME'];
			if (self::read('config.ingnore-subdomain', true))
				$server = preg_replace('#([^\/\.]+?\.[^\/\.]+)$#', '$1', $server);

			if (isset($val[$server]))
				self::multiWrite($val[$server]);
			elseif (class_exists('Debug', false) && self::read('core.debug') > 0)
				Debug::log("Undefined server configuration for '$server'.");

		} else {
			$levels = explode('.', $key);
			$level = & self::$config;
			foreach ($levels as $name) {
				if (!isset($level[$name]))
					$level[$name] = array();

				$level = & $level[$name];
			}

			$level = $val;
		}
	}


	/**
	 * Writes array of configuration
	 * @param array $config array of cofigurations pairs
	 * @return void
	 */
	public static function multiWrite($config)
	{
		foreach ((array) $config as $key => $val)
			self::write($key, $val);
	}


	/**
	 * Reads configuration value
	 * @param string $key key name
	 * @param mixed $default default value
	 * @return mixed
	 */
	public static function read($key, $default = null)
	{
		$key = strtolower($key);
		$levels = explode('.', $key);
		$level = & self::$config;

		foreach ($levels as $name) {
			if (isset($level[$name]))
				$level = & $level[$name];
			else
				return $default;
		}

		return $level;
	}


	/**
	 * Parses YAML configuration file
	 * @param string $file configuration file
	 * @throws RuntimeException
	 * @return array
	 */
	public static function parseFile($file)
	{
		if (!is_file($file))
			throw new RuntimeException("Missing configuration file '$file'.");

		$data = trim(file_get_contents($file));
		$data = preg_replace("#\t#", '    ', $data);
		$data = explode("\n", $data);
		return self::parseNode($data);
	}


	/**
	 * Parses config node
	 * @param string config node
	 * @return array
	 */
	protected static function parseNode($data)
	{
		$array = array();
		for ($i = 0, $to = count($data); $i < $to; $i++) {
			if (preg_match('#^([a-z0-9\-\.]+):(.*)$#Ui', trim($data[$i]), $match)) {
				if (empty($match[2])) {
					$node = array();
					while (isset($data[++$i]) && substr($data[$i], 0, 4) == '    ')
						$node[] = substr($data[$i], 4);

					--$i;
					$array[$match[1]] = self::parseNode($node);
				} else {
					if (preg_match('#\[[\'"](.+)[\'"](?:,\s[\'"](.+)[\'"])*\]#U', $match[2], $value))
						array_shift($value);
					else
						$value = trim(trim($match[2]), '\'"');

					$array[$match[1]] = $value;
				}
			}
		}

		return $array;
	}


}