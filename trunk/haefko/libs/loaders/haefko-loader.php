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
 * @subpackage  Loaders
 */


require_once dirname(__FILE__) . '/loader.php';


class HaefkoLoader extends Loader
{

	/** @var array - Available framework classes */
	protected $classes = array(
		# application
		'application'   => 'application/libs/application.php',
		'router'        => 'application/libs/router.php',
		'apptemplate'   => 'application/libs/app-template.php',
		# libs
		'cache'         => 'libs/cache.php',
		'config'        => 'libs/config.php',
		'control'       => 'libs/control.php',
		'datagrid'      => 'libs/data-grid.php',
		'debug'         => 'libs/debug.php',
		'object'        => 'libs/object.php',
		'form'          => 'libs/form.php',
		'html'          => 'libs/html.php',
		'http'          => 'libs/http.php',
		'l10n'          => 'libs/l10n.php',
		'paginator'     => 'libs/paginator.php',
		'session'       => 'libs/session.php',
		'tools'         => 'libs/tools.php',
		# templates
		'itemplate'     => 'libs/itemplate.php',
		'template'      => 'libs/template.php',
		'filterhelper'  => 'libs/template/filter-helper.php',
		'htmlhelper'    => 'libs/template/html-helper.php',
		'jshelper'      => 'libs/template/js-helper.php',
		'rsshelper'     => 'libs/template/rss-helper.php',
		# user
		'iidentity'     => 'libs/iidentity.php',
		'identity'      => 'libs/identity.php',
		'permission'    => 'libs/permission.php',
		'resource'     => 'libs/permission.php',
		'permissionassertion'    => 'libs/permission.php',
		'user'          => 'libs/user.php',
		'iuserhandler'  => 'libs/user.php',
		# database
		'db'            => 'libs/db.php',
		'dbstructure'   => 'libs/db-structure.php',
		'dbtable'       => 'libs/db-table.php',
		# loaders
		'autoloader'    => 'libs/loaders/auto-loader.php',
	);


	/**
	 * Loads file for required class
	 * @param string $class class name
	 * @return HaefkoLoader
	 */
	public function load($class)
	{
		$c = strtolower($class);
		if (strpos($c, 'controller') !== false)
			return Application::get()->loadControllerClass($class);

		if (isset($this->classes[$c]))
			require_once dirname(__FILE__) . '/../../' . $this->classes[$c];
	}


	/**
	 * Registers haefko loader
	 * @return AutoLoader
	 */
	public function register()
	{
		parent::register(array($this, 'load'));
		return $this;
	}


}