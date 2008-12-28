<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Application
 * @subpackage  View
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