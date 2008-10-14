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


interface IView
{


	function render();
	function set($name, $value);
	function __set($name, $value);
	function __get($name);
	function __isset($name);
	function __unset($name);


}