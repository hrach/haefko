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
 * @subpackage  View
 */


require_once dirname(__FILE__) . '/../../libs/template.php';


class View extends Template
{


	/** @var string */
	public $ext = 'phtml';

	/** @var array - Routing params for view path */
	protected $routing = array();

	/** @var string */
	protected $view;

	/** @var string */
	protected $layout = 'layout';


	/**
	 * Constrctor
	 * @return  void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->setVar('title', '');
		$this->getHelper('html');
		$this->getHelper('js');
	}


	/**
	 * Sets controller name fot templeta path
	 * @param   string
	 * @return  void
	 */
	public function setRouting($key, $controller)
	{
		$this->routing[$key] = $controller;
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
	 * Includes templatefile
	 * @param   string    filename
	 * @return  string
	 */
	public function load($file)
	{
		$file = Inflector::snippetViewFile($this->ext, $file);
		return parent::load($file);
	}


	/**
	 * Renders layout and view templates
	 * @return  string
	 */
	public function renderTemplates()
	{
		call_user_func(array(Controller::get(), 'prepareView'));
		$this->setFile($this->viewFactory());
		$this->setVar('content', $this->render(true));


		$layoutPath = $this->layoutFactory();
		if ($layoutPath === false)
			return $this->getVar('content');


		call_user_func(array(Controller::get(), 'prepareLayout'));		
		$layout = clone $this;
		$layout->setFile($this->layoutFactory());
		
		return $layout->render();
	}


	/**
	 * Creates layout template path
	 * @return  string|void
	 */
	private function layoutFactory()
	{
		if ($this->layout === false || $this->routing['ajax'])
			return false;

		$app = Application::get()->path . '/';
		$core = dirname(__FILE__) . '/../';

		$layouts = array(
			$app . Inflector::layoutFile($this->ext, $this->layout, $this->routing['module']),
			$app . Inflector::layoutFile($this->ext, $this->layout, ''),
			$core . Inflector::layoutFile($this->ext, $this->layout, ''),
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
	private function viewFactory()
	{
		$app = Application::get()->path . '/';
		$core = dirname(__FILE__) . '/../';

		# error views
		if (Application::$error) {
			$views = array(
				$app . Inflector::errorViewFile($this->ext, $this->view),
				$core . Inflector::errorViewFile('phtml', $this->view)
			);

			foreach ($views as $view) {
				if (file_exists($view))
					return $view;
			}

			throw new Exception("Missing error view '$views[0]'.");

		# normal views
		} else {
			$isService = !empty($this->routing['service']);
			if ($this->routing['ajax'])
				$ext = 'ajax.' . $this->ext;
			else
				$ext = $this->ext;


			$views = array(
				$app . Inflector::viewFile($ext, $this->view, $this->routing['module'], $this->routing['controller'], $isService),
				$core . Inflector::viewFile($ext, $this->view, '', $this->routing['controller'], $isService)
			);

			foreach ($views as $view) {
				if (file_exists($view))
					return $view;
			}

			throw new ApplicationException('missing-view', $view);
		}
	}


}