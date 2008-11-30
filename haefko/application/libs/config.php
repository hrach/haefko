<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko_Application
 */


class Config
{


	/** @var array */
	public static $config = array();


	/**
	 * Write configuration
	 * Where $key == 'servers' is parser only configuration for actual server-name
	 * @param   mixed   key name
	 * @param   mixed   value
	 * @return  void
	 */
	public static function write($key, $val)
	{
		if ($key == 'servers' && is_array($val)) {

			$server = $_SERVER['SERVER_NAME'];
			if (self::read('Config.www', true))
				$server = Tools::lTrim($server, 'www.');

			if (isset($val[$server]))
				self::multiWrite($val[$server]);
			else
				throw new Exception("Undefined server configuration for '$server'.");

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
	 * Multi configuration write
	 * @param   array
	 * @return  void
	 */
	public static function multiWrite($config)
	{
		foreach ((array) $config as $key => $val)
			self::write($key, $val);
	}


	/**
	 * Read configuration value
	 * If $key doesn't exists, return $default
	 * @param   string  key
	 * @param   mixed   default value
	 * @return  mixed
	 */
	public static function read($key, $default = null)
	{
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
	 * Parse YAML configuration file
	 * @param   string  filename
	 * @return  array
	 */
	public static function parseFile($file)
	{
		$data = trim(file_get_contents($file));
		$data = preg_replace("#\t#", '    ', $data);
		$data = explode("\n", $data);

		return self::parseNode($data);
	}


	/**
	 * Parse node
	 * @param   string  config node
	 * @return  array
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