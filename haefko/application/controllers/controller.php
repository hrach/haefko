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


abstract class Controller extends Object
{


	/**
	 *
	 * Returns self instance
	 * @return  Controller
	 */
	public static function get()
	{
		return Application::get()->controller;
	}


	/** @var bool - Allow include templates which are not situated in module */
	protected $allowTemplatePathReduction = false;

	/** @var array */
	protected $services = array(
		'rss' => array(
			'layout' => 'rss-layout',
			'helpers' => 'rss',
		),
		'xml' => array(
			'layout' => 'xml-layout',
		),
	);

	/** @var Application */
	private $application;

	/** @var ITemplate */
	private $template;

	/** @var stdClass */
	private $routing;


	/**
	 * Constructor
	 * @return  void
	 */
	public function __construct()
	{
		$this->application = Application::get();

		$this->routing = (object) array();
		$this->routing->controller = Tools::dash($this->application->router->controller);
		$this->routing->module = $this->application->router->module;
		$this->routing->action = $this->application->router->action;
		$this->routing->template = '';
		$this->routing->service = $this->application->router->service;
		$this->routing->ajax = Http::isAjax();
		$this->routing->ext = 'phtml';

		# TEMPLATE
		$this->template = $this->getTemplateInstace();
		if (!($this->template instanceof ITemplate))
			throw new LogicException('You must return template class which implements ITemplate interface.');

		# SERVICES & LAYOUT
		$layout = 'layout';
		if (isset($this->services[$this->routing->service])) {
			$service = $this->services[$this->routing->service];
			if (isset($service['layout']))
				$layout = $service['layout'];
			
			if (isset($service['helpers'])) {
				foreach ((array) $service['helpers'] as $helper)
					$this->template->getHelper($helper);
			}
		}

		$this->template->setExtendsFile($layout);
		$this->routing->layout = $layout;
	}


	/**
	 * Returns template class instance
	 * @return ITemplate
	 */
	protected function getTemplateInstace()
	{
		return new AppTemplate(null, $this->application->cache);
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
	 * Returns template instance
	 * @return ITemplate
	 */
	public function getTemplate()
	{
		return $this->template;
	}


	/**
	 * Returns template instance
	 * @return ITemplate
	 */
	public function getView()
	{
		trigger_error('View is deprecated', E_USER_WARNING);
		return $this->template;
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
	 * Jumps out of application and display error $template
	 * @param string $template error template name
	 * @param bool $debug is error page only for debug mode?
	 * @param int|null $errorCode
	 * @throws ApplicationError
	 * @return void
	 */
	public function error($template = '404', $debug = false, $errorCode = 404)
	{
		throw new ApplicationError($template, $debug, $errorCode);
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
	 * @return void
	 */
	public function render()
	{
		try {
			# INITS
			call_user_func(array($this, 'init'));
			if ($this->routing->template !== false)
				$this->routing->template = $this->routing->action;

			# METHOD
			$method = Tools::camelize($this->routing->action);
			if ($this->routing->ajax && method_exists($this, $method . 'AjaxAction'))
				$method .= 'AjaxAction';
			elseif (method_exists($this, $method . 'Action'))
				$method .= 'Action';
			else
				throw new ApplicationException('missing-method', $method);

			# CALL
			$args = $this->application->router->getArgs();
			unset($args['controller'], $args['module'], $args['action'], $args['service']);

			call_user_func(array($this, 'beforeAction'));
			$return = call_user_func_array(array($this, $method), $args);
			call_user_func(array($this, 'afterAction'));
			if ($this->routing->ajax && $return !== null)
				$this->proccessAjaxResponse($return);

			$this->template->setExtendsFile($this->routing->layout);
			$this->template->setFile($this->getTemplateFile($this->routing));

		} catch (ApplicationError $exception) {
			$template = '404';
			if (Config::read('core.debug') > 0)
				$template = $exception->errorFile;

			$this->template->setFile($this->getErrorTemplateFile($template));
		}

		return $this->template->render();
	}


	/**
	 * Sets view template to error template
	 * @param Exception $exception
	 */
	public function setErrorTemplate($template)
	{
		$this->template->setFile($this->getErrorTemplateFile($template));
		return $this;
	}


	/**
	 * Proccess method result - output result fox ajax
	 * @param mixes $return method result
	 */
	protected function proccessAjaxResponse($return)
	{
		if (!is_array($return)) {
			if (is_object($return))
				$return = (array) $return;
			else
				$return = array('response' => $return);
		}

		ob_clean();
		echo json_encode($return);
		exit;
	}


	/**
	 * Returns template relative path
	 * @param array $modules
	 * @param string $controller
	 * @param string $template
	 * @param string $service
	 * @param string $ext
	 * @return string
	 */
	protected function constructTemplatePath($modules, $controller, $template, $service, $ext)
	{
		$module = null;
		if (!empty($modules))
			$module = implode('-module/', $modules) . '-module/';
		if (!empty($service))
			$service = ".$service";

		return "/templates/$module$controller/$template$service.$ext";
	}


	/**
	 * Returns error template relative path
	 * @param string $errorTemplate
	 * @param string $ext
	 * @return string
	 */
	protected function constructErrorTemplatePath($errorTemplate, $ext)
	{
		return "/templates/_errors/$errorTemplate.$ext";
	}


	/**
	 * Returns template file path
	 * @throws ApplicationException
	 * @return string
	 */
	private function getTemplateFile($routing, $return = false)
	{
		$app = $this->application->path;
		$core = $this->application->corePath . '/application';

		$file = $this->constructTemplatePath($routing->module, $routing->controller,
			$routing->template, $routing->service, $routing->ext);
		if (file_exists($app . $file))
			return $app . $file;
		elseif (file_exists($core . $file))
			return $core . $file;

		$file1 = $this->constructTemplatePath(array(), $routing->controller,
			$routing->template, $routing->service, $routing->ext);
		if ($this->allowTemplatePathReduction && file_exists($app . $file1))
			return $app . $file1;
		if (file_exists($core . $file1))
			return $core . $file1;

		throw new ApplicationException('missing-view', $file);
	}


	/**
	 * Returns error template file path
	 * @param string $errorTemplate
	 * @throws RuntimeException
	 * @return string
	 */
	private function getErrorTemplateFile($errorTemplate)
	{
		$app = $this->application->path;
		$core = $this->application->corePath . '/application';

		$file = $this->constructErrorTemplatePath($errorTemplate, $this->routing->ext);
		if (file_exists($app . $file))
			return $app . $file;
		elseif (file_exists($core . $file))
			return $core . $file;

		throw new RuntimeException("Missing error template '$file'.");
	}


}