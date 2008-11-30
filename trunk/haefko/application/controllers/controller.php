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
 * @subpackage  Controller
 */


abstract class Controller extends Object
{

	/** @var string */
	private $urlPrefix;
	

	/** @var Application */
	protected $application;

	/** @var View */
	protected $view;

	/** @var bool Ajax request? */
	public $ajax = false;

	/** @var array */
	public $helpers = array();


	/**
	 * Returns self instance
	 * @return  Controller
	 */
	public static function get()
	{
		return Application::get()->controller;
	}


	/**
	 * Constructor
	 * @return  void
	 */
	public function __construct()
	{
		$this->application = Application::get();
		$this->ajax = Http::isAjax();

		# load view
		$class = 'View';
		if (!empty($this->application->router->service))
			$class = Config::read('Service.' . $this->application->router->service, 'View');
		$this->application->loadFile('views/' . Tools::dash($class) . '.php');
		$this->view = new $class($this);

		if ($this->ajax)
			$this->view->layout(false);
	}


	/**
	 * Activates support for section $section
	 * @param   string    section name (db|)
	 * @return  void
	 */
	public function initialize($section)
	{
		switch (strtolower($section)) {
			case 'db':
				$this->application->loadCore('db');
				Db::connect(Config::read('Db.connection', array()));

				$this->application->loadCore('db-table');
				$this->application->loadCore('db-table-structure');
				break;
			case 'translation':
			case 'translations':
				$this->application->loadCore('l10n');
				break;
		}
	}


	/**
	 * Returns Application instance
	 * @return  Application
	 */
	public function getApplication()
	{
		return $this->application;
	}


	/**
	 * Returns View instance
	 * @return  View
	 */
	public function getView()
	{
		return $this->view;
	}


	/**
	 * Returns Routing instance
	 * @return  Routing
	 */
	public function getRouting()
	{
		return $this->routing;
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
	 * Jumps out of application and display error $view
	 * @param   string    error view
	 * @param   bool      is error page only for debug mode?
	 * @return  void
	 */
	public function error($view = '404', $debug = false)
	{
		throw new ApplicationError($view, $debug);
	}


	/**
	 * Redirects to new $url and terminate application when $exit = true
	 * @param   string    internal relative url
	 * @param   bool      terminate application?
	 * @return  void
	 */
	public function redirect($url, $exit = true)
	{
		Http::headerRedirect($this->url($url, array(), true), 303);
		if ($exit) exit;
	}


	/**
	 * Creates application internal url
	 * @param   string  url
	 * @param   array   additional args
	 * @param   bool    absolute url?
	 * @return  string
	 */
	public function url($url, $args = array(), $absolute = false)
	{
		$url = $this->processUrl($url, (array) $args);

		if (isset($url[0]) && $url[0] !== '/')
			$url = $this->urlPrefix . $url;

		$url = '/' . ltrim($url, '/');
		if ($absolute)
			return Http::$serverUri . Http::$baseUri . $url;
		else
			return Http::$baseUri . $url;
	}


	/**
	 * Sets application internal url's prefix
	 * @param   string  url
	 * @return  void
	 */
	public function urlPrefix($url)
	{
		$this->urlPrefix = $this->processUrl($url);
	}


	/**
	 * Runns actino call
	 * @return  void
	 */
	public function render()
	{
		call_user_func(array($this, 'init'));
		$method = Tools::camelize($this->application->router->action);

		if ($this->ajax && method_exists(get_class($this), $method . 'AjaxAction'))
			$method .= 'AjaxAction';
		else
			$method .= 'Action';

		$exists = method_exists(get_class($this), $method);

		if ($exists && $this->view->getView() == '')
			$this->view->view($this->application->router->action);

		if (!$exists && !Application::$error)
			throw new ApplicationException('missing-method', $method);

		$this->view->loadHelpers();

		try {
			if ($exists) {
				$args = $this->application->router->getArgs();
				unset($args['controller'], $args['module'], $args['action'], $args['service']);
				call_user_func_array(array($this, $method), $args);
			}
		} catch (ApplicationError $e) {
			$this->view->view($e->view);
		}

		return $this->view->render();
	}


	/**
	 * Processes the application url
	 * @param   string    url
	 * @param   array     args
	 * @return  string
	 */
	private function processUrl($url, $args = array())
	{
		$args = array_merge($this->application->router->getArgs(), $args);
		$url = preg_replace('#\<\:(\w+)\>#e', 'isset($args["\\1"]) ? $args["\\1"] : "\\1"', $url);
		$url = preg_replace_callback('#\<\$url\>#', array('Http', 'getRequest'), $url);

		return $url;
	}


}