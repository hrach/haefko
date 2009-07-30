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
 */


interface IIdentity
{


	/**
	 * Returns user primary key
	 * @return mixed
	 */
	public function getId();


	/**
	 * Returns user roles
	 * @return array
	*/
	public function getRoles();


	/**
	 * Returns user data
	 * @return array
	 */
	public function getData();


}