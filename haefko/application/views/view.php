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
	 * Set view template
	 * @param   string
	 * @return  void
	 */
	public function view($view)
	{
		$this->view = $view;
	}


	/**
	 * Return view template
	 * @return  string
	 */
	public function getView()
	{
		return $this->view;
	}


	/**
	 * Set layout template
	 * @param   string|false
	 * @return  void
	 */
	public function layout($layout)
	{
		$this->layout = $layout;
	}


	/**
	 * Return layout template
	 * @return  string|false
	 */
	public function getLayout()
	{
		return $this->layout;
	}


	/**
	 * Set application theme
	 * @param   string|false
	 * @return  void
	 */
	public function theme($theme)
	{
		$this->theme = $theme;
	}


	/**
	 * Return theme
	 * @return  string|false
	 */
	public function getTheme()
	{
		return $this->theme;
	}


	/**
	 * Load heleper
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
	 * Nacte helpery definovane v controller
	 * @return  void
	 */
	public function loadHelpers()
	{
		$this->helper('html');
		foreach ($this->controller->helpers as $helper)
			$this->helper($helper);
	}



	/**
	 * Načtení externího kodu
	 * @param   string  jmeno souboru bez pripony
	 * @return  void
	 */
	public function renderSnippet($name)
	{
		$file = $this->controller->app->path . Inflector::snippetViewFile($this->ext, $name);

		if (file_exists($file)) {
			extract($this->vars);
			$controller = $this->controller;
			include $file;
		} else {
			die("Haefko: nenalezena sablona snippet view $file!");
		}
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
			throw new Exception("Neexistujici promenna \$$name!");
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
			$app  . Inflector::layoutFile($this->ext, $this->layout, Router::$namespace, $this->theme),
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

				$ajaxView = Inflector::viewFile("ajax.{$this->ext}", $this->view, Router::$namespace, $this->theme, Router::$controller, !empty(Router::$service));

				if (file_exists("{$app->path}/$ajaxView"))
					return "{$app->path}/$ajaxView";
				else
					return false;
			} else {

				$view = Inflector::viewFile($this->ext, $this->view, Router::$namespace, $this->theme, Router::$controller, !empty(Router::$service));

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
	 * Parsuje sablonu
	 * @param   string  cesta k sablone
	 * @param   array   promenne
	 * @return  string
	 */
	protected function parse($parsedFile)
	{
		extract($this->vars);
		extract($this->helpers);
		$controller = $this->controller;

		include $parsedFile;
		return ob_get_clean();
	}


}