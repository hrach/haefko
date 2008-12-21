<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko_Libs
 */


class Cache extends Object
{


	/** @var bool */
	public static $enabled = true;

	/** @var string */
	public static $store = '/temp/cache';

	/** @var int */
	public static $lifeTime = 18000;    # 5 minutes


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
			$lifeTime = self::$lifeTime;

		$file = $this->getFilename($group, $id);

		if ($fp = fopen($file, 'xb')) {
			if (flock($fp, LOCK_EX))
				fwrite($fp, $data);

			fclose($fp);
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

		if (self::$enabled && file_exists($file)) {
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
		return self::$store . $group . '_' . md5($id);
	}


}