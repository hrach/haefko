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
 */


class Inflector
{


	/**
	 * Returns controller's class name
	 * @param   string  controller
	 * @param   array   modules
	 * @return  string
	 */
	public static function controllerClass($controller, $module = null)
	{
		if (empty($module))
			$module = null;
		else
			$module = implode('_', $module) . '_';

		return $module . $controller . 'Controller';
	}


	/**
	 * Returns controller's file name
	 * @param   string  controlleru class
	 * @return  string
	 */
	public static function controllerFile($class)
	{
		$class = Tools::dash($class);
		$file = str_replace(array('_-', '_'), array('/', '/'), $class);

		return "controllers/$file.php";
	}


	/**
	 * Returns helper's class name
	 * @param   string  helper
	 * @return  string
	 */
	public static function helperClass($name)
	{
		return ucfirst(strtolower($name)) . 'Helper';
	}


	/**
	 * Returns helper's file name
	 * @param   string  helper class
	 * @return  string
	 */
	public static function helperFile($name)
	{
		return 'views/@helpers/' . Tools::dash($name) . '.php';
	}


	/**
	 * Returns layout's view file name
	 * @param   string       pripona
	 * @param   string       jmeno layoutu
	 * @param   string|bool  namespace
	 * @return  string
	 */
	public static function layoutFile($ext, $name, $module)
	{
		$path  = 'views/';


		if (!empty($module)) {
			foreach ((array) $module as $m)
				$path .= Tools::dash($m) . '-module/';
		}

		$path .= Tools::dash($name) .".$ext";

		return $path;
	}



	/**
	 * Returns view's file name
	 * @param   string       pripona
	 * @param   string       jmeno view
	 * @param   string|bool  namespace
	 * @param   string       controller
	 * @param   string       service
	 * @return  string
	 */
	public static function viewFile($ext, $name, $module, $controller, $service)
	{
		$path  = 'views/';

		if (!empty($module)) {
			foreach ((array) $module as $m)
				$path .= Tools::dash($m) . '-module/';
		}

		$path .= Tools::dash($controller) . '/';
		$path .= ($service) ? 'service/' : '';
		$path .= Tools::dash($name) . ".$ext";

		return $path;
	}



	/**
	 * Vrati jmeno souboru pro chybove view
	 * @param   string       pripona
	 * @param   string       jmeno view
	 * @return  string
	 */
	public static function errorViewFile($ext, $name)
	{
		$path  = "views/";
		$path .= "@errors/$name.$ext";

		return $path;
	}



	/**
	 * Vrati jmeno souboru pro snippet view
	 * @param   string       pripona
	 * @param   string       jmeno view
	 * @return  string
	 */
	public static function snippetViewFile($ext, $path)
	{
		return Application::get()->path . "/views/$path.$ext";
	}



}