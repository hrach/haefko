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
		array_walk($module, array('Tools', 'camelize'));
		return implode('', $module) . Tools::camelize($controller) . 'Controller';
	}


	/**
	 * Returns controller's file name
	 * @param   string  controlleru class
	 * @return  string
	 */
	public static function controllerFile($class)
	{
		return 'controllers/' . Tools::dash($class) . '.php';
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
		return 'views/helpers/' . Tools::dash($name) . '.php';
	}


	/**
	 * Returns layout's view file name
	 * @param   string       pripona
	 * @param   string       jmeno layoutu
	 * @param   string|bool  namespace
	 * @param   string|bool  theme
	 * @return  string
	 */
	public static function layoutFile($ext, $name, $module, $theme)
	{
		$path  = 'views/';
		$path .= ($theme) ? "$theme/" : '';
		if (!empty($module)) {
			array_walk($module, array('Tools', 'camelize'));
			$path .= implode('-', $module) .  '-';
		}
		$path .= Tools::dash($name) .".$ext";

		return $path;
	}



	/**
	 * Returns view's file name
	 * @param   string       pripona
	 * @param   string       jmeno view
	 * @param   string|bool  namespace
	 * @param   string|bool  theme
	 * @param   string       controller
	 * @param   string       service
	 * @return  string
	 */
	public static function viewFile($ext, $name, $module, $theme, $controller, $service)
	{
		$path  = 'views/';
		$path .= ($theme) ? "$theme/" : '';
		if (!empty($module)) {
			array_walk($module, array('Tools', 'camelize'));
			$path .= implode('-', $module) . '-';
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
	 * @param   string|bool  theme
	 * @return  string
	 */
	public static function errorViewFile($ext, $name, $theme)
	{
		$path  = "views/";
		$path .= ($theme) ? "$theme/" : '';
		$path .= "errors/$name.$ext";

		return $path;
	}



	/**
	 * Vrati jmeno souboru pro snippet view
	 * @param   string       pripona
	 * @param   string       jmeno view
	 * @param   string|bool  theme
	 * @return  string
	 */
	public static function snippetViewFile($ext, $path)
	{
		return "views/$path.$ext";
	}



}