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


/**
 * @author      Jan Skrasek, Zdenek Topic
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek, Zdenek Topic
 * @package     Haefko_Libs
 */
class Permission extends Object
{

	/** @var array */
	private $roles = array();

	/** @var array */
	private $resources = array(
		'*' => array()
	);


	/**
	 * Constructor
	 * @return Permission
	 */
	public function __construct()
	{
		$this->addRole('guest');
	}


	/**
	 * Adds role
	 * @param string $name role name
	 * @param array $parents role parents
	 * @return Permission
	 */
	public function addRole($name, $parents = array())
	{
		$this->roles[$name] = new PermissionRole($name, (array) $parents);
		return $this;
	}


	/**
	 * Adds resource
	 * @param string $name resource name
	 * @return Permission
	 */
	public function addResource($name)
	{
		$this->resources[$name] = true;
		return $this;
	}


	/**
	 * Checks whether role is in resource and action allowed
	 * @param array|string $role roles
	 * @param string $res resource name
	 * @param string $action
	 * @return bool
	 */
	public function isAllowed($role, $res, $action)
	{
		foreach ((array) $role as $val) {
			if ($this->roles[$val]->isDefined($res, $action))
				return $this->roles[$val]->isAllowed($res, $action);
			elseif ($this->roles[$val]->hasParents())
				return $this->isAllowed($this->roles[$val]->getParents(), $res, $action);
		}

		return false;
	}


	/**
	 * Allows roles in resources in actions
	 * @param array|string $roles
	 * @param array|string $resources
	 * @param array|string $actions allowed actions
	 * @return Permission
	 */
	public function allow($roles, $resources = '*', $actions = '*')
	{
		return $this->setAccess(true, $roles, $resources, $actions);
	}


	/**
	 * Denies roles in resources in actions
	 * @param array|string $roles
	 * @param array|string $resources
	 * @param array|string $actions denied actions
	 * @return Permission
	 */
	public function deny($roles, $resources = '*', $actions = '*')
	{
		return $this->setAccess(false, $roles, $resources, $actions);
	}


	/**
	 * Sets access for roles in resources in actions
	 * @param bool $access
	 * @param array|string $roles
	 * @param array|string $resources
	 * @param array|string $actions
	 * @return Permission
	 */
	protected function setAccess($access, $roles, $resources, $actions)
	{
		if ($resources == '*')
			$resources = array_keys($this->resources);

		foreach ((array) $roles as $role) {
			if (!isset($this->roles[$role]))
				throw new Exception("Undefined role '$role'.");

			foreach ((array) $resources as $resource) {
				if (!isset($this->resources[$resource]))
					throw new Exception("Undefined resource '$resource'.");

				foreach ((array) $actions as $action)
					$this->roles[$role]->setAccess($access, $resource, $action);
			}
		}

		return $this;
	}


}


/**
 * @author      Jan Skrasek, Zdenek Topic
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek, Zdenek Topic
 * @package     Haefko_Libs
 */
class PermissionRole extends Object
{

	/** @var string - Role name*/
	private $name;

	/** @var array */
	private $resources = array();

	/** @var array */
	private $parents = array();


	/**
	 * Constructor
	 * @param string $name role name
	 * @param array $parents
	 * @return PermissionRole
	 */
	public function __construct($name, $parents)
	{
		$this->name = $name;
		$this->parents = (array) $parents;
	}


	/**
	 * Checks whether action is allowed on resource
	 * @param string $res resource name
	 * @param string $action action name
	 * @return bool
	 */
	public function isAllowed($res, $action)
	{
		return $this->resources[$res][$action] || $this->resources['*'][$action]
		    || $this->resources[$res]['*'] || $this->resources['*']['*'];
	}


	/**
	 * Sets access for action on role
	 * @param bool $access
	 * @param string $res resource name
	 * @param string $action action name
	 * @return PermissionRole
	 */
	public function setAccess($access, $res, $action)
	{
		$this->resources[$res][$action] = $access;
		return $this;
	}


	/**
	 * Returns parents
	 * @return array
	 */
	public function getParents()
	{
		return $this->parents;
	}


	/**
	 * Returns true if role has any parents
	 * @return bool
	 */
	public function hasParents()
	{
		return count($this->parents) > 0;
	}


	/**
	 * Checks whether is set resource and action
	 * @param string $res resource name
	 * @param string $action action name
	 * @return bool
	 */
	public function isDefined($res, $action)
	{
		return (isset($this->resources[$res]) && (isset($this->resources[$res][$action]) || isset($this->resources[$res]['*'])))
		    || (isset($this->resources['*']) && (isset($this->resources['*'][$action]) || isset($this->resources['*']['*'])));
	}


}