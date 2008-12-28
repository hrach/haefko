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


	/** @var bool Ajax request? */
	public $isAjax = false;

	/** @var Application */
	private $application;

	/** @var View */
	private $view;

	/** @var string */
	private $urlPrefix;

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
	public function __construct($class = 'View')
	{
		$this->application = Application::get();
		$this->isAjax = Http::isAjax();

		# load view
		if (!empty($this->application->router->service) && !Application::$error)
			$class = Tools::camelize($this->application->router->service . 'View');

		$this->application->loadFile('views/' . Tools::dash($class) . '.php');
		$this->view = new $class($this);

		if ($this->isAjax)
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
				Db::connect(Config::read('Db.connection'));
				$this->application->loadCore('db-table');
				break;
			case 'l10n':
			case 'translation':
			case 'localization':
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


	/**#@+
	 * Empty callbacks
	 */
	public function init() {}
	public function beforeAction() {}
	public function afterAction() {}
	public function prepareView() {}
	public function prepareLayout() {}
	/**#@-*/

	/**
	 * Jumps out of application and display error $view
	 * @param   string    error view
	 * @param   bool      is error page only for debug mode?
	 * @param   boool     sent automaticly error headers?
	 * @return  void
	 */
	public function error($view = '404', $debug = false, $autoheaders = true)
	{
		throw new ApplicationError($view, $debug, $autoheaders);
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
		if ($exit)
			$this->application->terminate();
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


		try {
			# ajax call
			if ($this->isAjax && method_exists(get_class($this), $method . 'AjaxAction')) {
				$method .= 'AjaxAction';

				if ($this->view->getView() !== false)
					$this->view->view($this->application->router->action);

				$args = $this->application->router->getArgs();
				unset($args['controller'], $args['module'], $args['action'], $args['service']);

				call_user_func(array($this, 'beforeAction'));
				$return = call_user_func_array(array($this, $method), $args);
				call_user_func(array($this, 'afterAction'));

				if ($return !== null) {
					if (!is_array($return)) {
						if (is_object($return))
							$return = (array) $return;
						else
							$return = array('response' => $return);
					}

					echo json_encode($return);
					exit;
				}
			} else {
				$method .= 'Action';
				$exists = method_exists(get_class($this), $method);
				if (!$exists)
					throw new ApplicationException('missing-method', $method);

				if ($this->view->getView() !== false)
					$this->view->view($this->application->router->action);

				$args = $this->application->router->getArgs();
				unset($args['controller'], $args['module'], $args['action'], $args['service']);

				call_user_func(array($this, 'beforeAction'));
				call_user_func_array(array($this, $method), $args);
				call_user_func(array($this, 'afterAction'));
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
		$url = preg_replace('#(\<\:(\w+)\>)#e', 'isset($args["\\2"]) ? $args["\\2"] : "\\1"', $url);
		$url = preg_replace_callback('#\<\:url\:\>#', array('Http', 'getRequest'), $url);

		return $url;
	}


}