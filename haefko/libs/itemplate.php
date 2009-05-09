<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id: $
 * @package     Haefko_Libs
 */


interface ITemplate
{


	/**
	 * Sets variable
	 * @param   string    var name
	 * @param   mixed     value
	 * @return  Template  $this
	 */
	public function setVar($key, $val);


	/**
	 * Returns variable
	 * @param   string    var name
	 * @return  mixed
	 */
	public function getVar($key);


	/**
	 * Sets variables
	 * @param   array     variables
	 * @return  Template  $this
	 */
	public function setVars($vars);


	/**
	 * Returns variables
	 * @return  array
	 */
	public function getVars();


	/**
	 * Sets file name
	 * @param   string     filename
	 * @return  Template   $this
	 */
	public function setFile($file);


	/**
	 * Returns file name
	 * @param  string
	 */
	public function getFile();


	/**
	 * Renders template a return content
	 * @return  string
	 */
	public function render();


	/**#@+
	 * Interface methods
	 */
	public function __isset($name);
	public function __unset($name);
	public function __set($name, $value);
	public function __get($name);
	/**#@-*/


}