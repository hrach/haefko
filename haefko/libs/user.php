<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Libs
 */


require_once dirname(__FILE__) . '/object.php';


/**
 * @author      Jan Skrasek, Zdenek Topic
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek, Zdenek Topic
 * @package     Haefko_Libs
 */
class User extends Object
{

	/** @var SessionNamespace */
	protected $session;

	/** @var IUserHandler */
	protected $userHandler;


	/**
	 * Constructor
	 * @return User
	 */
	public function __construct()
	{
		$this->session = Session::getNamespace('auth.user');
		if (!$this->session->exists('authenticated')) {
			$this->session->authenticated = false;
			$this->session->id = null;
			$this->session->roles = array('guest');
			$this->session->data = (object) array();
		}
	}


	/**
	 * Returns true if user has rights for $action on $resource
	 * @param string resource name
	 * @param string aciton name
	 * @return bool
	 */
	public function isAllowed($res, $action)
	{
		return $this->acl->isAllowed($this->getRoles(), $res, $action);
	}


	/**
	 * Returns true if user is authenticated
	 * @return bool
	 */
	public function isAuthenticated()
	{
		return $this->session->authenticated;
	}


	/**
	 * Sets user handler for authentication
	 * @param string user handler class name
	 * @return User
	 */
	public function setUserHandler($handler)
	{
		$this->userHandler = $handler;
		return $this;
	}


	/**
	 * Authenticates by provided credentials
	 * @param string username
	 * @param string password
	 * @param array extra data
	 * @return bool
	 */
	public function authenticate($username, $password, $extra = array())
	{
		$handler = new $this->userHandler;
		$result = $handler->authenticate(array(
			'username' => $username,
			'password' => $password,
			'extra' => $extra
		));

		return $this->processIdentity($result);
	}


	/**
	 * Updates user indentity
	 * @throws Exception
	 * @return bool
	 */
	public function updateIndentity()
	{
		if (empty($this->session->id))
			throw new Exception('You can not update identity when user is not authenticated.');

		$handler = new $this->userHandler;
		$result = $handler->updateIdentity($this->session->id);
		return $this->processIdentity($result);
	}


	/**
	 * Sets user authentication expiration time
	 * @param int|string time expression
	 * @return User
	 */
	public function setExpiration($time)
	{
		$this->session->setExpiration($time);
		return $this;
	}


	/**
	 * Signs out user
	 * @return User
	 */
	public function signOut()
	{
		if ($this->isAuthenticated()) {
			$this->session->authenticated = false;
			$this->session->id = null;
			$this->session->roles = array('guest');
			$this->session->data = (object) array();
		}

		return $this;
	}


	/**
	 * Returns user roles
	 * @return array
	 */
	public function getRoles()
	{
		return $this->session->roles;
	}


	/**
	 * Returns true if user is in role $name
	 * @param $name role name
	 * @return bool
	 */
	public function isInRole($name)
	{
		return in_array($name, $this->getRoles());
	}


	/**
	 * Setter
	 * @param string property name
	 * @param mixed property value
	 * @return void
	 */
	public function __set($key, $val)
	{
		if ($key == 'id' || $key == 'roles')
			$this->session->$key = $val;
		else
			$this->session->data->$key = $val;
	}


	/**
	 * Getter
	 * @param string property name
	 * @return mixed
	 */
	public function __get($key)
	{
		if ($key == 'id' || $key == 'roles')
			return $this->session->read($key);
		elseif (isset($this->sesion->data->key))
			return $this->session->data->$key;
		else
			throw new Exception("Undefined user data variable '$key'.");
	}


	/**
	 * Proccesses user indentity
	 * @param false|Identity user identity
	 * @throws Exception
	 * @return bool
	 */
	protected function processIdentity($identity)
	{
		if ($identity === false)
			return false;
		elseif (!($identity instanceof IIdentity))
			throw new Exception('Result of UserHandler::authenticate() must implements IIdentity.');

		$this->session->authenticated = true;
		$this->session->id = $identity->getId();
		$this->session->roles = $identity->getRoles();
		$this->session->data = (object) $identity->getData();
		return true;
	}


}


/**
 * @author      Jan Skrasek, Zdenek Topic
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek, Zdenek Topic
 * @package     Haefko_Libs
 */
interface IUserHandler
{


	/**
	 * Returns user indentity or false
	 * @param array user credentials - array with keys username, password, extra
	 * @return IIdentity|false
	 */
	public function authenticate($credentials);


	/**
	 * Returns updated users identity
	 * @param mixed id - user primary key
	 * @return IIdentity
	 */
	public function updateIdentity($id);


}