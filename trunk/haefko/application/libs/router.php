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


class Router
{


	/** @var array */
	public static $defaults = array();

	/** @var array */
	public static $url = array();

	/** @var array */
	public static $routing = array(
		'controller' => '',
		'action' => '',
		'module' => array(),
		'args' => array()
	);

	/** @var string */
	public static $service;

	/** @var bool */
	public static $routed = false;


	/**
	 * Initializes router
	 * @return  void
	 */
	public static function initialize()
	{
		self::$url = self::toArray(Http::getRequest());
	}


	/**
	 * Adds service for alternate view
	 * @param   string  service name
	 * @return  bool
	 */
	public static function addService($service)
	{
		if (strcasecmp(end(self::$url), $service) === 0) {
			array_pop(self::$url);
			self::$service = strtolower($service);
			return true;
		}

		return false;
	}


	/**
	 * Connects to url
	 * @param   string  routing expression
	 * @param   array   defaults
	 * @return  bool
	 */
	public static function connect($route, $defaults = array())
	{
		if (self::$routed) return false;

		$routing = array_merge(array(
			'controller' => '',
			'action' => '',
			'module' => array()
		), self::$defaults, $defaults);
		$rule = self::toArray($route);
		if (end($rule) == ':args') {
			array_pop($rule);

			$i = 0;
			while (count($rule) < count(self::$url))
				$rule[] = ':arg' . $i++;
		}

		if (count($rule) < count(self::$url)) return false;

		foreach ($rule as $x => $e) {
			if (empty(self::$url[$x]))
				$val = '';
			else
				$val = self::$url[$x];

			# variable
			if (preg_match('#:(:)?(\w+)({(.*)})?#', $e, $match)) {
				if (!empty($match[3])) {
					if (empty($match[4]) && empty($val))
						continue;
					elseif (empty($match[4]))
						$match[4] = '#.+#';
					elseif ($match[4][0] != '#')
						$match[4] = "#^$match[4]$#i";

					if (!preg_match($match[4], $val))
						return false;
				} elseif (empty($val)) {
					return false;
				}

				if ($match[1] == ':')
					$val = Tools::lTrim($val, $match[2] . ':');

				if ($match[2] == 'module')
					$routing[$match[2]][] = $val;
				else
					$routing[$match[2]] = $val;
			# expression
			} elseif ($e != $val) {
				return false;
			}
		}

		if (empty($routing['controller']))
			return false;

		if (empty($routing['action']))
			$routing['action'] = 'index';

		self::$routing['controller'] = strtolower($routing['controller']);
		self::$routing['action'] = strtolower($routing['action']);
		self::$routing['module'] = (array) $routing['module'];
		unset($routing['controller'], $routing['action'], $routing['module']);

		self::$routing['args'] = $routing;
		return self::$routed = true;
	}


	/**
	 * Transforms url string to array
	 * @param   array
	 * @return  string
	 */
	public static function toArray($url)
	{
		$url = trim($url, '/');
		if (empty($url))
			return array();
		else
			return explode('/', $url);
	}


}


Router::initialize();