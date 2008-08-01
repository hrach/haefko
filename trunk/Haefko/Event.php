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
 * Trida pro ukladani callbacku a jejich vyvolani
 */
class Event
{

	/** @var array */
	private static $events = array();



	/**
	 * Prida callback
	 * @param   string        jmeno udalosti
	 * @param   string|array  callback
	 * @return  void
	 */
	public static function add($event, $callback)
	{
		if (is_callable($callback)) {
			if (!isset(self::$events[$event]))
				self::$events[$event] = array();

			self::$events[$event][] = $callback;
		}
	}



	/**
	 * Zavola callbacky eventu a preda jim potrebne argumentry
	 * @param   string  jmeno eventu
	 * @param   array   argumenty
	 * @return  void
	 */
	public static function invoke($event, $args)
	{
		if (!isset(self::$events[$event]))
			return;

		foreach (self::$events[$event] as $callback)
			call_user_func_array($callback, $args);
	}



}