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



require_once dirname(__FILE__) . '/iview.php';


class View extends Object implements IView
{


	/** @var string */
	public $ext = 'phtml';

	/** @var string */
	protected $base;

	/** @var CustomController */
	protected $controller;

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
		$this->base = Http::$baseUri;

		$this->set('escape', 'htmlSpecialChars');
		$this->set('title', $this->controller->getClass());
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
	 * Loads heleper
	 * @param   string  helper name
	 * @param   string  var name
	 */
	public function helper($name, $var = null)
	{
		if (is_null($var))
			$var = strtolower($name);

		if (!isset($this->helpers[$var])) {
			$class = Inflector::helperClass($name);
			$this->controller->app->loadClass('helper', $class);
			$this->helpers[$var] = new $class;
		}

		return $this->helpers[$var];
	}



	/**
	 * Loads defined helpers in controller
	 * @return  void
	 */
	public function loadHelpers()
	{
		$this->helper('html');
		foreach ($this->controller->helpers as $helper)
			$this->helper($helper);
	}


	/**
	 * Loads snippet
	 * @param   string  filename without ext
	 * @throws  ApplicationException
	 * @return  void
	 */
	public function renderSnippet($name)
	{
		$file = $this->controller->app->path . Inflector::snippetViewFile($this->ext, $name);

		if (!file_exists($file))
			throw new ApplicationException('missing-view', $file);

		extract($this->vars);
		extract($this->helpers);
		$controller = Controller::i();
		$application = Application::i();

		require $file;
	}


	/**
	 * Ulozi do seznamu promennych pro sablonu
	 * Probiha s kontrolou ochrany promenne
	 * @param   string  jmeno promenne
	 * @param   mixed   hodnota promenne
	 * @return  void
	 */
	public function set($name, $value)
	{
		if (empty($name))
			throw new Exception('Nelze nastavit hodnotu nejmenne promenne!');

		$this->vars[$name] = $value;
	}


	/**
	 * Je nastavena promenna
	 * @param   string  jmenno promenne
	 * @return  boll
	 */
	public function __isset($name)
	{
		return isset($this->vars[$name]);
	}



	/**
	 * Smaze promennou
	 * @param   string  jmeno promenne
	 * @return  void
	 */
	public function __unset($name)
	{
		if (isset($this->protected[$name]))
			throw new Exception("Nelze smazat hodnotu chranene promenne \$$name!");

		unset($this->vars[$name]);
	}



	/**
	 * Ulozi do seznamu promennych pro sablonu
	 * @param   string  jmeno promenne
	 * @param   mixed   hodnota promenne
	 * @return  void
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value, false);
	}



	/**
	 * Vrati hodnotu z promennych pro sablonu
	 * @param   string  jmeno promenne
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
	 * Vyrenderuje view sablonu
	 * @return  void
	 */
	public function render()
	{
		if ($this->view === false)
			return;

		$viewPath = $this->viewFactory();
		call_user_func(array($this->controller, 'prepareView'));
		$view = $this->parse($viewPath);

		$layoutPath = $this->layoutFactory();
		if ($layoutPath === false)
			return $view;

		$this->vars['content'] = $view;
		call_user_func(array($this->controller, 'prepareLayout'));
		return $this->parse($layoutPath);
	}


	/**
	 * Vytvori cestu k layout sablone
	 * @return  string
	 */
	protected function layoutFactory()
	{
		if ($this->layout === false)
			return false;

		$app = Application::i()->path . '/';
		$core = dirname(__FILE__) . '/../';

		$layouts = array(
			$app  . Inflector::layoutFile($this->ext, $this->layout, Router::$routing['module'], $this->theme),
			$app  . Inflector::layoutFile($this->ext, $this->layout, '', ''),
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
	 * Vytvori cestu k view sablone
	 * @return  string
	 */
	protected function viewFactory()
	{
		$app = Application::i()->path . '/';
		$core = dirname(__FILE__) . '/../';

		if (!Application::$error) {
			if ($this->controller->ajax) {

				$ajaxView = Inflector::viewFile("ajax.{$this->ext}", $this->view, Router::$routing['module'], $this->theme, Router::$routing['controller'], !empty(Router::$service));

				if (file_exists("$app$ajaxView"))
					return "$app$ajaxView";
				else
					return false;
			} else {

				$view = Inflector::viewFile($this->ext, $this->view, Router::$routing['module'], $this->theme, Router::$routing['controller'], !empty(Router::$service));

				if (file_exists("$app$view"))
					return "$app$view";
				else
					throw new ApplicationException('missing-view', $view);

			}
		} else {
			$views = array(
				$app  . Inflector::errorViewFile($this->ext, $this->view, $this->theme),
				$core . Inflector::errorViewFile('phtml', $this->view, '')
			);

			foreach ($views as $view) {
				if (file_exists($view))
					return $view;
			}

			throw new Exception("Missing error view '$views[0]'.");
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
		$controller = Controller::i();
		$application = Application::i();

		include $__file__;
		$return = ob_get_contents();
		ob_clean();
		return $return;
	}


}