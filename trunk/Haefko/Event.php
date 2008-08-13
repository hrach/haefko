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
	 * Add callback
	 * @param   string        event name
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
	 * Delete callback
	 * @param   string        event name
	 * @param   string|array  callback
	 * @return  void
	 */
	public static function remove($event, $callback)
	{
		foreach (self::$events[$event] as $e => $c) {
			if ($callback == $c) {
				unset(self::$events[$event][$e]);
				break;
			}
		}
	}



	/**
	 * Call event callbacks with arguments
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