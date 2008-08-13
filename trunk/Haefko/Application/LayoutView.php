<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.8
 * @package     Haefko
 */



require_once dirname(__FILE__) . '/View.php';



/**
 * Trida View obstarava nacitani view a layoutu
 */
class LayoutView extends View
{

	/** @var string Cesta k layout view */
	private $layoutPath;

	/** @var string Jmeno layout view */
	private $layoutName = 'layout';



	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}



	/**
	 * Nastavi jmeno layout sablony
	 * @param   string  jemeno sablony (false == zadna sablona)
	 * @return  void
	 */
	public function layout($layoutName)
	{
		$this->layoutName = $layoutName;
	}



	/**
	 * Vrati jmeno layout sablony, bez pripony
	 * @return  string
	 */
	public function getLayout()
	{
		return $this->layoutName;
	}



	/**
	 * Vrati cestu k view layoutu
	 * @return  string
	 */
	public function getLayoutPath()
	{
		return $this->layoutPath;
	}



	/**
	 * Vyrenderuje stranku z view a layoutu
	 * @return  void
	 */
	public function render()
	{
		$this->vars['content'] = parent::render();
		$this->layoutPath = $this->layoutPathFactory();

		if ($this->layoutName === false) {
			return $this->vars['content'];
		} else {
			call_user_func(array($this->controller, 'prepareLayout'));
			return $this->parse($this->layoutPath, $this->vars);
		}
	}



	/**
	 * Vytvori cestu k layout sablone
	 * @return  string
	 */
	protected function layoutPathFactory()
	{
		$app = Application::getInstance();

		$layouts = array(
			"{$app->path}/" . Inflector::layoutFile($this->ext, $this->layoutName, Router::$namespace, $this->themeName),
			"{$app->path}/" . Inflector::layoutFile($this->ext, $this->layoutName, '', ''),
			"{$app->corePath}/Application/" . Inflector::layoutFile($this->ext, $this->layoutName, '', ''),
			"{$app->corePath}/Application/" . Inflector::layoutFile('phtml', 'layout', '', ''),
		);

		foreach ($layouts as $layout) {
			if (file_exists($layout))
				return $layout;
		}

		$this->layoutName = false;
	}



}