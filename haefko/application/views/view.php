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
 * @subpackage  View
 */


require_once dirname(__FILE__) . '/iview.php';


class View extends Object implements IView
{


	/** @var string */
	public $ext = 'phtml';

	/** @var string */
	protected $base;

	/** @var Controller */
	protected $controller;

	/** @var Application */
	protected $application;

	/** @var array */
	protected $vars = array();

	/** @var array */
	protected $helpers = array();

	/** @var string */
	protected $view;

	/** @var string */
	protected $layout = 'layout';

	/** @var string */
	protected $theme;


	/**
	 * Constrctor
	 * @return  void
	 */
	public function __construct(& $controller)
	{
		$this->controller = $controller;
		$this->application = Application::get();
		$this->base = Http::$baseUri;

		$this->set('escape', 'htmlSpecialChars');
		$this->set('title', '');

		$this->helper('html');
		$this->helper('js');
	}


	/**
	 * Sets view template
	 * @param   string
	 * @return  void
	 */
	public function view($view)
	{
		$this->view = $view;
	}


	/**
	 * Returns view template
	 * @return  string
	 */
	public function getView()
	{
		return $this->view;
	}


	/**
	 * Sets layout template
	 * @param   string|false
	 * @return  void
	 */
	public function layout($layout)
	{
		$this->layout = $layout;
	}


	/**
	 * Returns layout template
	 * @return  string|false
	 */
	public function getLayout()
	{
		return $this->layout;
	}


	/**
	 * Sets application theme
	 * @param   string|false
	 * @return  void
	 */
	public function theme($theme)
	{
		$this->theme = $theme;
	}


	/**
	 * Returns theme
	 * @return  string|false
	 */
	public function getTheme()
	{
		return $this->theme;
	}


	/**
	 * Returns base
	 * @return  string
	 */
	public function getBase()
	{
		return $this->base;
	}


	/**
	 * Returns controller
	 * @return  Controller
	 */
	public function getController()
	{
		return $this->controller;
	}


	/**
	 * Loads heleper
	 * @param   string    helper name
	 * @param   string    var name
	 */
	public function helper($name, $var = null)
	{
		if (is_null($var))
			$var = strtolower($name);

		if (!isset($this->helpers[$var])) {
			$class = Inflector::helperClass($name);
			$this->controller->application->loadClass('helper', $class);
			$this->helpers[$var] = new $class;
		}

		return $this->helpers[$var];
	}


	/**
	 * Loads snippet
	 * @param   string    filename without ext
	 * @throws  ApplicationException
	 * @return  void
	 */
	public function renderSnippet($name)
	{
		$file = $this->controller->application->path . '/' . Inflector::snippetViewFile($this->ext, $name);
		if (!file_exists($file))
			throw new ApplicationException('missing-view', $file);

		extract($this->vars);
		extract($this->helpers);
		$controller = Controller::get();
		$application = Application::get();

		require $file;
	}


	/**
	 * Checks whether the variable is set
	 * @param   string    var name
	 * @return  boll
	 */
	public function __isset($name)
	{
		return isset($this->vars[$name]);
	}


	/**
	 * Unsets variable value
	 * @param   string    var name
	 * @return  void
	 */
	public function __unset($name)
	{
		unset($this->vars[$name]);
	}


	/**
	 * Sets variable value
	 * @param   string    var name
	 * @param   mixed     var value
	 * @return  void
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}


	/**
	 * Sets variable value
	 * @param   string    var name
	 * @param   mixed     var value
	 * @throws  Exception
	 * @return  void
	 */
	public function set($name, $value)
	{
		if (empty($name))
			throw new Exception('You can\'t set variable with empty name.');

		$this->vars[$name] = $value;
	}


	/**
	 * Returns variable value
	 * @param   string    var name
	 * @return  mixed
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->vars))
			return $this->vars[$name];
		else
			parent::__get($name);
	}


	/**
	 * Renders web templates
	 * @return  void
	 */
	public function render()
	{
		if ($this->view === false)
			return;

		$viewPath = $this->viewFactory();
		call_user_func(array($this->controller, 'prepareView'));
		$render = $this->parse($viewPath);

		$layoutPath = $this->layoutFactory();
		if ($layoutPath !== false) {
			$this->vars['content'] = $render;
			call_user_func(array($this->controller, 'prepareLayout'));
			$render = $this->parse($layoutPath);
		}

		return $render;
	}


	/**
	 * Creates layout template path
	 * @return  string|void
	 */
	protected function layoutFactory()
	{
		if ($this->layout === false || $this->controller->isAjax)
			return false;

		$app = $this->application->path . '/';
		$core = dirname(__FILE__) . '/../';

		$layouts = array(
			$app . Inflector::layoutFile($this->ext, $this->layout, $this->application->router->module, $this->theme),
			$app . Inflector::layoutFile($this->ext, $this->layout, '', $this->theme),
			$core . Inflector::layoutFile($this->ext, $this->layout, '', ''),
			$core . Inflector::layoutFile('phtml', 'layout', '', '')
		);

		foreach ($layouts as $layout) {
			if (file_exists($layout))
				return $layout;
		}

		return false;
	}


	/**
	 * Creates view template path
	 * @return  string
	 */
	protected function viewFactory()
	{
		$app = $this->application->path . '/';
		$core = dirname(__FILE__) . '/../';

		# error views
		if (Application::$error) {
			$views = array(
				$app . Inflector::errorViewFile($this->ext, $this->view, $this->theme),
				$core . Inflector::errorViewFile('phtml', $this->view, '')
			);

			foreach ($views as $view) {
				if (file_exists($view))
					return $view;
			}

			throw new Exception("Missing error view '$views[0]'.");

		# normal views
		} else {

			# ajax
			if ($this->controller->isAjax)
				$view = Inflector::viewFile('ajax.'. $this->ext, $this->view, $this->application->router->module,
				                            $this->theme, $this->application->router->controller,
				                            !empty($this->application->router->service));
			# normal
			else
				$view = Inflector::viewFile($this->ext, $this->view, $this->application->router->module,
				                            $this->theme, $this->application->router->controller,
				                            !empty($this->application->router->service));

			if (file_exists("$app$view"))
				return "$app$view";

			throw new ApplicationException('missing-view', $view);
		}
	}


	/**
	 * Parses view template
	 * @param   string  template filename
	 * @return  string
	 */
	protected function parse($__file__)
	{
		extract($this->vars);
		extract($this->helpers);
		$controller = Controller::get();
		$application = Application::get();

		include $__file__;
		$return = ob_get_contents();
		ob_clean();

		if (Config::read('View.filterInternalUrl', false))
			$return = preg_replace('#(<[^>]+ (src|href|action))\s*=\s*"(hf://)#i', '$1="' . (empty($this->base) ? '/' : $this->base), $return);

		return $return;
	}


}