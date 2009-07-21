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


class Cache extends Object implements ArrayAccess
{

	/** @var bool */
	public $enabled;

	/** @var string */
	public $storage;

	/** @var array */
	protected $meta = array();


	/**
	 * Constructor
	 * @param bool $enabled
	 * @param string $storage temp cache storage path
	 * @return Cache
	 */
	public function __construct($enabled = true, $storage = './temp')
	{
		$this->enabled = $enabled;
		$this->storage = rtrim($storage, '/') . '/';
	}


	/**
	 * Writes data to cache
	 * @param string $key key name
	 * @param mixed $data
	 * @param array $options
	 * @return void
	 */
	public function save($key, $data, $options = array())
	{
		if (!$this->enabled) return false;
		if (!is_string($data)) {
			$data = serialize($data);
			$options['serialized'] = true;
		}

		if (isset($options['files'])) {
			$files = (array) $options['files'];
			$options['files'] = array();
			foreach ($files as $file)
				$options['files'][$file] = filemtime($file);
		}

		$this->write($key, $data, $options);
	}


	/**
	 * Reads cached data by key
	 * @param string $key key name
	 * @return mixed|null
	 */
	public function read($key)
	{
		if (!$this->enabled) return null;
		if (!isset($this->meta[$key]))
			$this->isCached($key);

		if (!$this->meta[$key]['cached'])
			return null;

		return $this->readCache($key);
	}


	/**
	 * Checks if is key cached
	 * @param string $key key file name
	 * @return bool
	 */
	public function isCached($key)
	{
		if (!$this->enabled) return false;
		if (isset($this->meta[$key]['cached']))
			return $this->meta[$key]['cached'];

		$this->meta[$key]['cached'] = false;
		$file = $this->getFilename($key);
		if (!file_exists($file))
			return false;

		$header = $this->readHeader($file);
		if (!$this->isValid(array(), $header))
			return false;

		$this->meta[$key]['header'] = $header;
		$this->meta[$key]['cached'] = true;
		return true;
	}


	/**
	 * Deletes $key cache
	 * @param string $key key file name
	 * @return bool
	 */
	public function delete($key)
	{
		if (!$this->enabled) return true;
		$file = $this->getFilename($key);
		if (file_exists($file))
			return unlink($file);

		return true;
	}


	/**
	 * Cleans invalid cache
	 * @param array $conds clean up conditions
	 * @return Cache
	 */
	public function clean($conds = array())
	{
		$dir = new DirectoryIterator($this->storage);
		foreach ($dir as $file) {
			if ($file->isDir()) continue;
			$header = $this->readHeader($file->getPathname());
			if (!$this->isValid($conds, $header))
				unlink($file->getPathname());
		}

		return $this;
	}


	/**
	 * Array-access interface
	 */
	public function offsetSet($key, $value)
	{
		$this->save($key, $value);
	}


	/**
	 * Array-access interface
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->read($key);
	}


	/**
	 * Array-access interface
	 */
	public function offsetUnset($key)
	{
		$this->delete($key);
	}


	/**
	 * Array-access interface
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->isCached($key);
	}


	/**
	 * Returns path with filename of cached file
	 * @param string $key key file name
	 * @return string
	 */
	public function getFilename($key)
	{
		return $this->storage . 'cache_' . $key;
	}


	/**
	 * Writes data to cache
	 * @param string $key key name
	 * @param string $data serialized data
	 * @param array $header header meta information
	 * @return void
	 */
	protected function write($key, $data, $header)
	{
		$this->meta[$key]['header'] = $header;
		$this->meta[$key]['cached'] = true;

		$file = $this->getFilename($key);
		$header = serialize($header);
		$data = '<?php ## ' . str_pad((string) strlen($header), 6, '0', STR_PAD_LEFT)
		      . $header . " ?>\n" . $data;

		if ($fp = @fopen($file, 'w+b')) {
			if (flock($fp, LOCK_EX))
				fwrite($fp, $data);

			fclose($fp);
		}
	}


	/**
	 * Reads header information
	 * @param string $file file path
	 * @return array
	 */
	protected function readHeader($file)
	{
		if (($fp = fopen($file, 'r'))) {
			$length = fread($fp, 15);
			$length = (int) substr($length, 9);
			$header = fread($fp, $length);
			fclose($fp);
			return unserialize($header);
		}

		return false;
	}


	/**
	 * Reads cached data
	 * @param string $key file key name
	 * @return string
	 */
	protected function readCache($key)
	{
		$file = $this->getFilename($key);
		$content = file_get_contents($file);
		$content = substr($content, strpos($content, "\n"));
		if ($this->meta[$key]['header']['serialized'])
			$content = unserialize(trim($content));

		return $content;
	}


	/**
	 * Checks if is the file valid
	 * @param array $conds
	 * @param array $header
	 * @return bool
	 */
	protected function isValid($conds, $header)
	{
		if (isset($header['expire'])) {
			if ($header['expire'] < time())
				return false;
		}

		if (isset($header['files']))
		foreach ((array) $header['files'] as $file => $time) {
			$fileNow = @filemtime($file);
			if ($fileNow != $time)
				return false;
		}

		return true;
	}


}