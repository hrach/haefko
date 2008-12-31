<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Application
 */


class Router
{


	/** @var bool */
	public $routed = false;

	/** @var array */
	protected $defaults = array();

	/** @var array */
	protected $url = array();

	/** @var array */
	protected $routing = array();


	/**
	 * Initializes router
	 * @return  void
	 */
	public function __construct()
	{
		$this->url = Http::getRequest();
	}


	/**
	 * Sets defaults routing setting
	 * @param   array     defaults settings
	 * @return  Router    $this
	 */
	public function defaults($defaults = null)
	{
		$this->defaults = (array) $defaults;
		return $this;
	}


	/**
	 * Catchs service request
	 * @param   string    service name
	 * @return  bool
	 */
	public function service()
	{
		$services = func_get_args();
		foreach ($services as $service) {
			$service = strtolower($service);
			if ($this->url == $service || Tools::endWith($this->url, "/$service")) {
				$this->url = trim(Tools::rTrim($this->url, "$service"), '/');
				$this->routing['service'] = $service;
				return true;
			}
		}

		return false;
	}


	/**
	 * Connects to url
	 * @param   string    routing expression
	 * @param   array     defaults
	 * @param   bool      base route?
	 * @return  bool
	 */
	public function connect($route, $defaults = array(), $baseRoute = false)
	{
		# route only once
		if ($this->routed)
			return false;


		# set the defaults
		$newRoute = '';
		$route = trim($route, '/');
		$routing = array('controller' => '', 'action' => 'index', 'module' => array());
		$routing = $this->normalize(array_merge($this->routing, $routing, $this->defaults, $defaults));


		# explode by variables
		$parts = preg_split('#\<\:\w+( [^>]+)?\>#', $route);
		if (count($parts) > 1) {
			preg_match_all('#\<\:(\w+)( [^>]+)?\>#', $route, $matches);
			foreach ($matches[2] as $i => $match) {
				if (empty($match))
					$match = $baseRoute ? '[^/]+' : '[^/]+?';

				# escape other text
				$newRoute .= preg_quote($parts[$i], '#') . '(' . trim($match) . ')';
			}

			if (!empty($parts[$i + 1]))
				$newRoute .= $parts[$i + 1];
		} else {
			$newRoute = $route;
		}


		if ($baseRoute === false) {
			# match url and routing
			if (!preg_match("#^$newRoute$#", $this->url, $m))
				return false;

			array_shift($m);
			$this->routing = $routing;
			if (count($m) > 0) {
				foreach ($matches[1] as $i => $key) {
					if ($key == 'module')
						$this->routing['module'][] = $m[$i];
					else
						$this->routing[$key] = $m[$i];
				}
			}
		} else {
			if (!preg_match("#^$newRoute#", $this->url, $m, PREG_OFFSET_CAPTURE))
				return false;

			array_shift($m);
			$this->routing = $routing;
			if (count($m) > 0) {
				foreach ($matches[1] as $i => $key) {
					if ($key == 'module')
						$this->routing['module'][] = $m[$i][0];
					else
						$this->routing[$key] = $m[$i][0];
				}
			}

			$lastChar = end($m);
			$lastChar = strlen($lastChar[0]) + $lastChar[1];

			if (strlen($this->url) > $lastChar) {
				$args = explode('/', substr($this->url, $lastChar + 1));
				foreach ($args as $i => $arg)
					$this->routing['var' . ($i + 1)] = $arg;
			}
		}


		$this->routing = $this->normalize($this->routing);
		return $this->routed = true;
	}


	/**
	 * Returns args array
	 * @return  array
	 */
	public function getArgs()
	{
		return $this->routing;
	}


	/**
	 * Return arg
	 * @param   strign    arg name
	 * @param   bool      inline named variable?
	 * @return  mixed     if doesn't exists return null
	 */
	public function get($key, $named = true)
	{
		if (!$named) {
			if (!array_key_exists($key, $this->routing))
				return null;

			return $this->routing[$key];
		}

		foreach ($this->routing as $value) {
			if (is_string($value) && Tools::startWith($value, "$key:"))
				return Tools::lTrim($value, "$key:");
		}

		return null;
	}


	/**
	 * Getter
	 * @param   string   variable name
	 * @return  mixed
	 */
	public function __get($key)
	{
		if (!array_key_exists($key, $this->routing))
			return null;

		return $this->routing[$key];
	}


	/**
	 * Setter
	 * @param   string    variable name
	 * @param   mixed     value
	 * @throws  Exception
	 * @return  void
	 */
	public function __set($key, $value)
	{
		if (empty($key))
			throw new Exception ('You can\'t set routing variable with empty name.');

		$this->routing[$key] = $value;
	}


	/**
	 * Issetter
	 * @return  bool
	 */
	public function __isset($key)
	{
		return array_key_exists($key, $this->routing);
	}


	/**
	 * Unsetter
	 * @throws  Exception
	 */
	public function __unset($key)
	{
		throw new Exception("You can't unset 'Router::\$$key' variable.");
	}


	/**
	 * Normalizes routing array
	 * @param   array
	 * @return  array
	 */
	private function normalize($routing)
	{
		$routing['module'] = (array) $routing['module'];
		$routing['controller'] = Tools::camelize($routing['controller']);
		$routing['action'] = lcfirst(Tools::camelize($routing['action']));

		if (!isset($routing['service']))
			$routing['service'] = '';
		else
			$routing['service'] = strtolower($routing['service']);

		foreach ($routing['module'] as & $module)
			$module = Tools::camelize($module);

		return $routing;
	}


}