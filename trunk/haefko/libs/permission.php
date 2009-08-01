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
 */
class Permission extends Object
{

	/** @var array */
	private $roles = array();

	/** @var array */
	private $resources = array(
		'*' => array(),
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
		$this->roles[$name] = new PermissionRole($this, $name, (array) $parents);
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
	 * @param string $action action name
	 * @return bool
	 */
	public function isAllowed($role, $res, $action = null)
	{
		if (empty($action)) $action = '*';
		$resName = (string) $res;

		foreach ((array) $role as $val) {
			if ($this->roles[$val]->isDefined($resName, $action))
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
	 * @param PermissionAssertion $pAssertion
	 * @return Permission
	 */
	public function allow($roles, $resources = null, $actions = null, PermissionAssertion $pAssertion = null)
	{
		return $this->setAccess(true, $roles, $resources, $actions, $pAssertion);
	}


	/**
	 * Denies roles in resources in actions
	 * @param array|string $roles
	 * @param array|string $resources
	 * @param array|string $actions denied actions
	 * @param PermissionAssertion $pAssertion
	 * @return Permission
	 */
	public function deny($roles, $resources = null, $actions = null, PermissionAssertion $pAssertion = null)
	{
		return $this->setAccess(false, $roles, $resources, $actions, $pAssertion);
	}


	/**
	 * Sets access for roles in resources in actions
	 * @param bool $access
	 * @param array|string $roles
	 * @param array|string $resources
	 * @param array|string $actions
	 * @param PermissionAssertion $pAssertion
	 * @return Permission
	 */
	protected function setAccess($access, $roles, $resources, $actions, $pAssertion)
	{
		if (empty($actions)) $actions = '*';
		if ($resources == '*' || empty($resources)) $resources = array_keys($this->resources);

		foreach ((array) $roles as $role) {
			if (!isset($this->roles[$role]))
				throw new Exception("Undefined role '$role'.");

			foreach ((array) $resources as $resource) {
				if (!isset($this->resources[$resource]))
					throw new Exception("Undefined resource '$resource'.");

				foreach ((array) $actions as $action)
					$this->roles[$role]->setAccess($access, $resource, $action, $pAssertion);
			}
		}

		return $this;
	}


}


/**
 * @author      Jan Skrasek, Zdenek Topic
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek, Zdenek Topic
 */
class PermissionRole extends Object
{

	/** @var Permission */
	private $permission;

	/** @var string - Role name*/
	private $name;

	/** @var array */
	private $resources = array();

	/** @var array */
	private $parents = array();


	/**
	 * Constructor
	 * @param Permission $permission
	 * @param string $name role name
	 * @param array $parents
	 * @return PermissionRole
	 */
	public function __construct(Permission $permission, $name, $parents)
	{
		$this->name = $name;
		$this->parents = (array) $parents;
		$this->permission = $permission;
	}


	/**
	 * Checks whether action is allowed on resource
	 * @param string $res resource name
	 * @param string $action action name
	 * @return bool
	 */
	public function isAllowed($res, $action)
	{
		$resName = (string) $res;
		if ($action == '*') {
			foreach (array_keys($this->resources[$resName]) as $act) {
				$result = $this->isAllowedWithAssertion($res, $act);
				if ($result) return true;
			}
		} else {
			if (isset($this->resources[$resName][$action]))
				return $this->isAllowedWithAssertion($res, $action);
			elseif (isset($this->resources[$resName]['*']))
				return $this->isAllowedWithAssertion($res, '*');
			elseif (isset($this->resources['*'][$action]))
				return $this->resources['*'][$action][0];
			elseif (isset($this->resources['*']['*']))
				return $this->resources['*']['*'][0];
		}

		return false;
	}


	/**
	 * Checks if is access allowed with dynamic permission
	 * @param string|Resource $res
	 * @param string $action
	 * @return bool
	 */
	protected function isAllowedWithAssertion($res, $action)
	{
		$resName = (string) $res;
		$result = $this->resources[$resName][$action][0];

		if (!$result)
			return false;
		if (empty($this->resources[$resName][$action][1]))
			return $result;

		$pAssertion = $this->resources[$resName][$action][1];
		return $pAssertion->setResource($res)->assert($this->permission, $resName, $action);
	}


	/**
	 * Sets access for action on role
	 * @param bool $access
	 * @param string $res resource name
	 * @param string $action action name
	 * @param PermissionAssertion $pAssertion
	 * @return PermissionRole
	 */
	public function setAccess($access, $res, $action, $pAssertion)
	{
		$this->resources[$res][$action][0] = $access;
		$this->resources[$res][$action][1] = $pAssertion;
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
		if ($action == '*')
			return isset($this->resources[$res]);
		else
			return (isset($this->resources[$res][$action]) || isset($this->resources[$res]['*']))
			    || (isset($this->resources['*'][$action]) || isset($this->resources['*']['*']));
	}


}


abstract class Resource extends Object
{

	/** @var string - Resource name */
	protected $name;


	/**
	 * Constructor
	 * @throws Exception
	 * @return Resource
	 */
	public function __construct()
	{
		if (empty($this->name))
			throw new Exception('Rousource name must be defined.');
	}


	/**
	 * toString interface
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}


}


abstract class PermissionAssertion extends Object
{

	/** @var Resource */
	protected $resource;


	/**
	 * Sets resource
	 * @param Resource $resource
	 * @return PermissionAssertion
	 */
	public function setResource(Resource $resource)
	{
		$this->resource = $resource;
		return $this;
	}


	/**
	 * Dynamic assertion
	 * @param Permission $acl
	 * @param string $resource
	 * @param string $action
	 * @return bool
	 */
	abstract public function assert(Permission $acl, $resource, $action);


}