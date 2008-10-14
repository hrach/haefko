<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko
 */


abstract class Cache
{

	public static $enabled = false;
	public static $store = 'temp/cache';

	public static function write($group, $id, $data, $expires = null)
	{
		if (empty($expires))
			$expires = 60 * 60;

		$file = self::getFilename($group, $id);

		if ($fp = fopen($file, 'xb')) {
			if (flock($fp, LOCK_EX))
				fwrite($fp, $data);

			fclose($fp);
			touch($file, time() + $expires);
		}
	}


	protected static function read($group, $id)
	{
		$file = self::getFilename($group, $id);
		return file_get_contents($file);
	}


	protected static function isCached($group, $id)
	{
		$file = self::getFilename($group, $id);

		if (self::$enabled && file_exists($file)) {
			if (filemtime($file) > time())
				return true;
			else
				unlink($file);
		}

		return false;
	}

	public static function get($group, $id)
	{
		if (self::isCached($group, $id))
			return unserialize(self::read($group, $id));

		return null;
	}

	public static function put($group, $id, $data, $expires = null)
	{
		self::write($group, $id, serialize($data), $expires);
	}

	protected static function getFilename($group, $id)
	{
		return self::$store . $group . '_' . md5($id);
	}

}