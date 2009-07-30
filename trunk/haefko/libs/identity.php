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


require_once dirname(__FILE__) . '/object.php';


class Identity extends Object implements IIdentity
{

	/** @var mixed - User primary key */
	protected $id;

	/** @var array */
	protected $roles = array();

	/** @var array - User data */
	protected $data = array();


	/**
	 * Constructor
	 * @param mixed user primary key
	 * @param array|string user roles
	 * @param array optional user data
	 * @return Indentity
	 */
	public function __construct($id, $roles = array('guest'), $data = array())
	{
		$this->id = $id;
		$this->roles = (array) $roles;
		$this->data = (array) $data;
	}


	/**
	 * Returns user primary key
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Returns user roles
	 * @return  array
	*/
	public function getRoles()
	{
		return $this->roles;
	}


	/**
	 * Returns user data
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}


}