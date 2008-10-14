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



/**
 * Trida Inflector spravuje jmenne konvence
 */
class Inflector
{



	/**
	 * Vrati jmeno tridy pro controller
	 * @param   string  controller
	 * @param   string  namespace
	 * @return  string
	 */
	public static function controllerClass($controller, $namespace = null)
	{
		return Tools::camelize($namespace) . Tools::camelize($controller) . 'Controller';
	}



	/**
	 * Vrati jmeno souboru pro controller
	 * @param   string  trida controlleru
	 * @return  string
	 */
	public static function controllerFile($class)
	{
		return 'controllers/' . Tools::dash($class) . '.php';
	}



	/**
	 * Vrati jmeno tridy pro model
	 * @param   string  model
	 * @param   string  namespace
	 * @return  string
	 */
	public static function sqlClass($model, $namespace = null)
	{
		return Tools::camelize($namespace) . Tools::camelize($model) . 'Model';
	}



	/**
	 * Vrati jmeno souboru pro model
	 * @param   string  trida modelu
	 * @return  string
	 */
	public static function sqlFile($class)
	{
		return 'sqls/' . Tools::dash($class) . '.php';
	}



	/**
	 * Vrati jmeno tridy pro helper
	 * @param   string  helper
	 * @return  string
	 */
	public static function helperClass($name)
	{
		return ucfirst(strtolower($name)) . 'Helper';
	}



	/**
	 * Vrati jmeno souboru pro helper
	 * @param   string  trida helperu
	 * @return  string
	 */
	public static function helperFile($name)
	{
		return 'views/helpers/' . Tools::dash($name) . '.php';
	}



	/**
	 * Vrati jmeno souboru pro layout
	 * @param   string       pripona
	 * @param   string       jmeno layoutu
	 * @param   string|bool  namespace
	 * @param   string|bool  theme
	 * @return  string
	 */
	public static function layoutFile($ext, $name, $namespace, $theme)
	{
		$path  = 'views/';
		$path .= ($theme) ? "$theme/" : '';
		$path .= ($namespace) ? Tools::dash($namespace) . '-' : '';
		$path .= Tools::dash($name) .".$ext";

		return $path;
	}



	/**
	 * Vrati jmeno souboru pro view
	 * @param   string       pripona
	 * @param   string       jmeno view
	 * @param   string|bool  namespace
	 * @param   string|bool  theme
	 * @param   string       controller
	 * @param   string       service
	 * @return  string
	 */
	public static function viewFile($ext, $name, $namespace, $theme, $controller, $service)
	{
		$path  = 'views/';
		$path .= ($theme) ? "$theme/" : '';
		$path .= ($namespace) ? Tools::dash($namespace) . '-' : '';
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