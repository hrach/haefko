<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Application
 */


class Router
{


	/** @var bool */
	public $routed = false;

	/** @var array */
	protected $defaults = array();


	/** @var string */
	protected $url;

	/** @var string */
	protected $urlParams;


	/** @var array */
	protected $routing = array();

	/** @var array */
	protected $vars = array();

	/** @var array */
	protected $params = array();


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
	public function connect($route, $defaults = array(), $baseRoute = false, $queryParams = false)
	{
		# route only once
		if ($this->routed)
			return false;

		if ($queryParams) {
			$qm = strpos($this->url, '?');
			if ($qm !== false) {
				$this->urlParams = substr($this->url, $qm + 1);
				$this->url = rtrim(substr($this->url, 0, $qm), '/');

				$query = array();				
				if (!empty($this->urlParams))
					$query = explode('/', $this->urlParams);

				foreach ($query as $param) {
					list ($key, $val) = explode(':', $param);
					$this->params[$key] = $val;
				}
			}
		}


		# set the defaults
		$newRoute = '';
		$route = trim($route, '/');
		$routing = array('controller' => '', 'action' => 'index', 'module' => array());
		$routing = $this->normalize(array_merge($this->routing, $routing, $this->defaults, (array) $defaults));


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


		# match url and routing
		if ($baseRoute === false) {
			if (!preg_match("#^$newRoute$#", $this->url, $m))
				return false;

			array_shift($m);
			$this->routing = $routing;
			if (count($m) > 0) {
				foreach ($matches[1] as $i => $key) {
					$this->set($key, $m[$i]);
				}
			}
		} else {
			if (!preg_match("#^$newRoute#", $this->url, $m, PREG_OFFSET_CAPTURE))
				return false;

			array_shift($m);
			$this->routing = $routing;
			if (count($m) > 0) {
				foreach ($matches[1] as $i => $key) {
					$this->set($key, $m[$i][0]);
				}
			}

			$lastChar = end($m);
			$lastChar = strlen($lastChar[0]) + $lastChar[1];

			if (strlen($this->url) > $lastChar) {
				$args = explode('/', substr($this->url, $lastChar + 1));
				foreach ($args as $i => $arg)
					$this->vars[($i + 1)] = $arg;
			}
		}


		$this->routing = $this->normalize($this->routing);
		return $this->routed = true;
	}


	/**
	 * Processes the application url
	 * @param   string    url
	 * @param   array     args
	 * @return  string
	 */
	public function url($url, $params = array(), $args = array())
	{
		if (!empty($params)) {
			$url = $this->urlParams;
			foreach ($params as $key => $val) {
				if ($val === null) {
					$url = preg_replace("#/?$key:(?:[^/]+)#", '', $url);
				} else {
					if (preg_match("#/?$key:([^/]+)#", $url, $matches))
						$url = preg_replace("#/?$key:[^/]+#", "/$key:$val", $url);
					else
						$url .= "/$key:$val";
				}
			}

			if (empty($url))
				$url = $this->url;
			else
				$url = $this->url . '/?' . ltrim($url, '/');

		} else {
			$args = array_merge($this->vars, $args);

			$url = preg_replace('#(\<\:(\w+)\>)#e', 'isset($args["\\2"]) ? $args["\\2"] : "<:\\2>"', $url);
			$url = preg_replace('#(\<\:(controller|action|service)\>)#e', 'isset($this->routing["\\2"]) ? Tools::dash($this->routing["\\2"]) : "<:\\2>"', $url);
			$url = preg_replace_callback('#\<\:(module(?:\[(\d+)\])?)\>#', array($this, 'moduleCb'), $url);
			$url = preg_replace_callback('#\<\:url\:\>#', array('Http', 'getRequest'), $url);

		}

		return $url;
	}


	/**
	 * Returns args array
	 * @return  array
	 */
	public function getArgs()
	{
		return $this->vars;
	}


	/**
	 * Returns arg or params
	 * @param   string    arg name
	 * @return  mixed     if arg doesn't exists returns null
	 */
	public function get($key)
	{
		if (array_key_exists($key, $this->vars))
			return $this->vars[$key];

		if (array_key_exists($key, $this->params))
			return $this->params[$key];

		return null;
	}


	/**
	 * Getter
	 * @param   string   variable name
	 * @return  mixed
	 */
	public function __get($key)
	{
		if (array_key_exists($key, $this->routing))
			return $this->routing[$key];

		if (array_key_exists($key, $this->vars))
			return $this->vars[$key];

		if (array_key_exists($key, $this->params))
			return $this->params[$key];

		return null;
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
	 * Module callback
	 * @param   array     matches
	 * @return  string
	 */
	public function moduleCb($matches)
	{
		if ($matches[1] == 'module')
			return Tools::dash(implode('/', $this->routing['module']));
		elseif (isset($this->routing['module'][$matches[2]]))
			return Tools::dash($this->routing['module'][$matches[2]]);
		else
			return null;
	}


	/**
	 * Normalizes routing array
	 * @param   array
	 * @return  array
	 */
	private function normalize($routing)
	{
		$def = array('controller' => '', 'action' => 'index', 'module' => array());
		$routing = array_merge($def, $routing);
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


	/**
	 * Sets routing/variable/param
	 * @param   string  name
	 * @param   mixed   value
	 * @return  void
	 */
	private function set($key, $val)
	{
		static $routing = array('controller', 'action', 'service');

		if ($key == 'module')
			$this->routing['module'][] = $val;
		elseif (in_array($key, $routing))
			$this->routing[$key] = $val;
		else
			$this->vars[$key] = $val;
	}


}