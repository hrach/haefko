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


abstract class Controller extends Object
{


	/** @var Application */
	protected $app;

	/** @var View */
	protected $view;

	/** @var bool Ajax request? */
	public $ajax = false;

	/** @var array */
	public $helpers = array();


	public static function i()
	{
		return Application::i()->controller;
	}


	/**
	 * Constructor
	 * @return  void
	 */
	public function __construct()
	{
		$this->app = Application::i();
		$this->ajax = Http::isAjax();

		# load view
		$class = Config::read('Service.' . Router::$service, 'View');
		$this->app->loadFile('views/' . Tools::dash($class) . '.php');
		$this->view = new $class($this);

		if ($this->ajax)
			$this->view->layout(false);
	}


	/**
	 * Activates support for section $section
	 * @param   string    section name (db)
	 * @return  void
	 */
	public function initialize($section)
	{
		switch (strtolower($section)) {
			case 'db':
				$this->app->loadCore('db', false);
				$config = Config::read('Db.connection', array());
				Db::connect($config);

				$this->app->loadCore('db-table');
				$this->app->loadCore('db-table-structure');
				break;
		}
	}


	/**
	 * Returns argument
	 * @param   string  arg name
	 * @return  mixed
	 */
	public function get($arg)
	{
		if (isset(Router::$routing['args'][$arg]))
			return Router::$routing['args'][$arg];
		else
			null;
	}


	/**
	 * Returns application instance
	 * @return  Application
	 */
	public function getApp()
	{
		return $this->app;
	}


	/**
	 * Returns view instance
	 * @return  View
	 */
	public function getView()
	{
		return $this->view;
	}


	/**
	 * Metoda init je zavolana vzdy pred zavolanim action
	 */
	public function init()
	{}



	/**
	 * Metoda renderInit je zavolana vzdy pred vyrenderovanim sablony, po zavolani action
	 */
	public function prepareView()
	{}



	/**
	 * Metoda prepareLayout je zavolana vzdy pred vyrenderovanim layout sablony
	 */
	public function prepareLayout()
	{}



	/**
	 * Zobrazi chybovou chybovou zpravu.
	 * Pokud je ladici rezim vypnut, zobrazi se chyba 404.
	 * @param   string  jmeno view
	 * @param   bool    nahradit v non-debug 404?
	 * @return  void
	 */
	public function error($view = '404', $debug = false)
	{
		throw new ApplicationError($view, $debug);
	}



	/**
	 * Presmeruje na novou url v ramci aplikace
	 * @param   string  url (relativni)
	 * @param   bool    zavolat po presmerovani exit?
	 * @return  void
	 */
	public function redirect($url, $exit = true)
	{
		Http::headerRedirect($this->url($url, true), 303);
		if ($exit)
			exit;
	}



	/**
	 * Vytvori URL v ramci aplikace
	 * @param   string  url
	 * @param   bool    absolutni url?
	 * @return  string
	 */
	public function url($url, $absolute = false)
	{
		$url = preg_replace('#\{url\}#', Http::getRequest(), $url);
		$url = preg_replace('#\{\:(\w+)\}#e', 'isset(Router::$routing["args"]["\\1"]) ? Router::$routing["args"]["\\1"] : "\\0"', $url);
		$url = preg_replace('#\{args\}#e', 'implode("/", Router::$routing["args"])', $url);
		$url = preg_replace_callback('#\{args!(.+)\}#', array($this, 'urlArgs'), $url);
		$url = '/' . trim($url, '\\/');

		if ($absolute)
			return Http::$serverUri . Http::$baseUri . $url;
		else
			return Http::$baseUri . $url;
	}



	/**
	 * Vrati hodnotu jmenneho argumentu
	 * @param   string  jmeno argumentu
	 * @param   mixed   defaultni hodnota
	 * @param   mixed   bool/string - jedna se o jemnny argument/odstranic dany prefix
	 * @return  mixed
	 */
	public function getArg($variable, $default = null, $name = null)
	{
		if (isset(Router::$args[$variable])) {
			if (empty($name))
				return Tools::lTrim(Router::$args[$variable], "$variable:");
			else
				return Tools::lTrim(Router::$args[$variable], "$name:");
		} else {
			return $default;
		}
	}



	/**
	 * Spusti volani action a rendering
	 * @return  void
	 */
	public function render()
	{
		$method = Tools::camelize(Router::$routing['action']);

		if ($this->ajax && method_exists(get_class($this), $method . 'AjaxAction'))
			$method .= 'AjaxAction';
		else
			$method .= 'Action';

		$exists = method_exists(get_class($this), $method);

		if ($exists && $this->view->getView() == '')
			$this->view->view(Router::$routing['action']);

		if (!$exists && !Application::$error)
			throw new ApplicationException('missing-method', $method);

		$this->view->loadHelpers();

		try {
			call_user_func(array($this, 'init'));
			if ($exists)
				call_user_func_array(array($this, $method), Router::$routing['args']);
		} catch (ApplicationError $e) {
			$this->view->view($e->view);
		}

		return $this->view->render();
	}



	/**
	 * Vrati cas url s pozadovanymi argumenty
	 * @param   array   matches
	 * @return  string
	 */
	private function urlArgs($matches)
	{
		$args = array();
		$matches = array_diff(array_keys(Router::$args), explode(',', $matches[1]));

		foreach ($matches as $match)
			$args[] = Router::$args[$match];

		return implode('/', $args);
	}



}