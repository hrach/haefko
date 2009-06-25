<?php

require_once dirname(__FILE__) . '/loader.php';
require_once dirname(__FILE__) . '/../tools.php';



class HaefkoLoader extends Loader
{

	
	/** @var array - Available framework classes */
	protected $classes = array(
		# application
		'application'  => 'application.php',
		'inflector'    => 'application/libs/inflector.php',
		'router'       => 'application/libs/router.php',
		# libs
		'cache'        => 'libs/cache.php',
		'config'       => 'libs/config.php',
		'cookie'       => 'libs/cookie.php',
		'datagrid'     => 'libs/data-grid.php',
		'debug'        => 'libs/debug.php',
		'object'       => 'libs/object.php',
		'form'         => 'libs/form.php',
		'html'         => 'libs/html.php',
		'http'         => 'libs/http.php',
		'l10n'         => 'libs/l10n.php',
		'paginator'    => 'libs/paginator.php',
		'permission'   => 'libs/permission.php',
		'session'      => 'libs/session.php',
		'template'     => 'libs/template.php',
		'tools'        => 'libs/tools.php',
		'user'         => 'libs/user.php',
		# database
		'db'           => 'libs/db.php',
		'dbstructure'  => 'libs/db-structure.php',
		'dbtable'      => 'libs/db-table.php',		
		# loaders
		'autoloader'   => 'libs/loaders/auto-loader.php',
	);


	/**
	 * Loads file for required class
	 * @param   string    class name
	 * @return  void
	 */
	public function load($class)
	{
		$c = strtolower($class);
		if (Tools::endWith($c, 'controller'))
			return Application::get()->loadClass('controller', $class);
		elseif (Tools::endWith($c, 'helper'))
			return Application::get()->loadClass('helper', $class);

		if (isset($this->classes[$c]))
			require_once dirname(__FILE__) . '/../../' . $this->classes[$c];
	}


	/**
	 * Registers haefko loader
	 * @return  void
	 */
	public function register()
	{
		parent::register(array($this, 'load'));
	}


}