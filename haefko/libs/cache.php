<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Libs
 */


require_once dirname(__FILE__) . '/object.php';


class Cache extends Object
{


	/** @var bool */
	public $enabled;

	/** @var string */
	public $store;

	/** @var int */
	public $lifeTime;


	/**
	 * Constructor
	 * @param   bool      enabled?
	 * @param   string    cache path
	 * @param   int|bool  lifeTime - default 5 minutes
	 * @return  void
	 */
	public function __construct($enabled = true, $store = './', $lifeTime = 18000)
	{
		$this->enabled = $enabled;
		$this->store = $store;
		$this->lifeTime = $lifeTime;
	}


	/**
	 * Writes data to cache
	 * @param   string    group name
	 * @param   string    id
	 * @param   mixed     data
	 * @param   mixed
	 * @return  void
	 */
	public function write($group, $id, $data, $expires = null)
	{
		$this->__write($group, $id, serialize($data), $expires);
	}


	/**
	 * Reads unserialized cached data
	 * @param   string      group name
	 * @param   string      id
	 * @return  mixed|null  return null when data are not cached
	 */
	public function read($group, $id)
	{
		if ($this->isCached($group, $id))
			return unserialize($this->__read($group, $id));

		return null;
	}


	/**
	 * Writes data to cache
	 * @param   string    group name
	 * @param   string    id
	 * @param   string    serialized data
	 * @param   int       time
	 * @return  void
	 */
	protected function __write($group, $id, $data, $lifeTime = null)
	{
		if (empty($expires))
			$lifeTime = $this->lifeTime;

		$file = $this->getFilename($group, $id);

		if ($fp = fopen($file, 'w+b')) {
			if (flock($fp, LOCK_EX))
				fwrite($fp, $data);

			fclose($fp);
			if ($lifeTime !== false)
				touch($file, time() + $lifeTime);
		}
	}


	/**
	 * Reads cached data
	 * @param   string      group name
	 * @param   string      id
	 * @return  string
	 */
	protected function __read($group, $id)
	{
		$file = $this->getFilename($group, $id);
		return file_get_contents($file);
	}


	/**
	 * Checks whether data are cached
	 * @param   string      group name
	 * @param   string      id
	 * @return  void
	 */
	protected function isCached($group, $id)
	{
		$file = $this->getFilename($group, $id);

		if ($this->enabled && file_exists($file)) {
			if (filemtime($file) > time())
				return true;
			else
				unlink($file);
		}

		return false;
	}


	/**
	 * Returns path with filename of cached file
	 * @param   string    group name
	 * @param   string    id
	 * @return  string
	 */
	protected function getFilename($group, $id)
	{
		return $this->store . $group . '_' . md5($id);
	}


}