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

		$this->view->setRouting('controller', $this->application->router->controller);
		$this->view->setRouting('module', $this->application->router->module);
		$this->view->setRouting('service', $this->application->router->service);
		$this->view->setRouting('ajax', $this->isAjax);

		if ($this->isAjax)
			$this->view->layout(false);
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
	 * Controller callbacks
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
	 * @param   int|null  error code
	 * @return  void
	 */
	public function error($view = '404', $debug = false, $errorCode = 404)
	{
		throw new ApplicationError($view, $debug, $errorCode);
	}


	/**
	 * Redirects to new $url and terminate application when $exit = true
	 * @param   string    internal relative url
	 * @param   bool      terminate application?
	 * @return  void
	 */
	public function redirect($url, $exit = true)
	{
		Http::headerRedirect($this->url($url, array(), array(), true), 303);
		if ($exit)
			exit;
	}


	/**
	 * Creates application internal url
	 * @param   string  url
	 * @param   array   additional args
	 * @param   bool    create absolute url?
	 * @return  string
	 */
	public function url($url, $params = array(), $args = array(), $absolute = false)
	{
		$url = $this->application->router->url($url, (array) $params, (array) $args);
		$url = '/' . ltrim($url, '/');

		if ($absolute)
			return Http::$serverUri . Http::$baseUri . $url;
		else
			return Http::$baseUri . $url;
	}


	/**
	 * Runns actino call
	 * @return  void
	 */
	public function render()
	{
		try {
			call_user_func(array($this, 'init'));
			$method = Tools::camelize($this->application->router->action);


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

		return $this->view->renderTemplates();
	}


}