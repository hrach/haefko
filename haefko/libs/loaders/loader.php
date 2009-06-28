<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Loaders
 */


require_once dirname(__FILE__) . '/../object.php';


abstract class Loader extends Object
{


	/** @var array */
	private $callbacks = array();


	/**
	 * Register callback for loader hanledr
	 * @param   mixed  $callback
	 * @return  void
	 */
	public function register($callback)
	{
		if (!is_callable($callback))
			throw new Exception('Loader callback is not callable');

		if (empty($this->callbacks))
			spl_autoload_register(array($this, 'autoloadHandler'));

		$this->callbacks[] = $callback;
	}


	/**
	 * Autoload handler
	 * @param   string    class name
	 * @return  void
	 */
	public function autoloadHandler($class)
	{
		foreach ($this->callbacks as $cb) {
			call_user_func($cb, $class);
			if (class_exists($class, false))
				break;
		}
	}


}