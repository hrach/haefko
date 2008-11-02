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


class Autoload
{


	/** @var array Allowed extension */
	public $exts = array('php');

	/** @var string Cache file */
	public $cache = 'autoload.dat';

	/** @var bool Automatic rebuild when missing class? */
	public $autoRebuild = false;

	/** @var bool Can rebuild? */
	public $rebuild = false;

	/** @var array */
	private $classes = array();

	/** @var array */
	private $files = array();

	/** @var array */
	private $dirs = array();


	/**
	 * Contructor
	 * Registers autoload
	 * @return  void
	 */
	public function __construct()
	{
		spl_autoload_register(array($this, 'autoloadHandler'));
	}


	/**
	 * Adds direcotry for scan
	 * @param   string     path
	 * @throws  Exception
	 * @return  Autoload
	 */
	public function addDir($dir)
	{
		if (!is_dir($dir))
			throw new Exception("Direcotory '$dir' doesn't exists.");

		$this->dirs[] = $dir;
		return $this;
	}


	/**
	 * Autoload handler - loads file with $class, or rebuild cache
	 * @param   string  class name
	 * @return  void
	 */
	public function autoloadHandler($class)
	{
		if (isset($this->classes[$class]) && file_exists($this->classes[$class]))
			require_once $this->classes[$class];
		elseif (!$this->rebuild && $this->autoRebuild)
			$this->rebuild();
	}


	/**
	 * Rebuilds cache list
	 * @throws  Exception
	 * @return  Autoload
	 */
	public function rebuild()
	{
		$this->findClasses();
		$this->rebuild = true;

		$cache = dirname($this->cache);
		if (!is_dir($cache))
			throw new Exception("Cache directory '$cache' doesn't exists.");

		file_put_contents($this->cache, serialize($this->classes));
		return $this;
	}


	/**
	 * Loads list of cached classes or create it
	 * @return  Autoload
	 */
	public function load()
	{
		if (file_exists($this->cache))
			$this->classes = unserialize(file_get_contents($this->cache));
		else
			$this->rebuild();

		return $this;
	}


	/**
	 * Finds all files and theirs classes
	 * @return  void
	 */
	private function findClasses()
	{
		$this->files = array();
		$this->classes = array();

		foreach ($this->dirs as $dir)
			$this->getFiles(new RecursiveDirectoryIterator($dir));

		foreach ($this->files as $file) {
			$catch = false;
			foreach (token_get_all(file_get_contents($file)) as $token) {
				if (is_array($token)) {
					if ($token[0] == T_CLASS || $token[0] == T_INTERFACE) {
						$catch = true;
					} elseif ($token[0] == T_STRING && $catch) {
						$this->classes[$token[1]] = $file;
						$catch = false;
					}
				}
			}
		}
	}


	/**
	 * Recursive DirectoryIterator handler
	 * @param   RecursiveDirectoryIterator
	 * @return  void
	 */
	private function getFiles(& $rdi)
	{
		$exts = '#\.(' . implode('|', $this->exts) . ')$#i';

		for ($rdi->rewind(); $rdi->valid(); $rdi->next()) {
			if ($rdi->isDot())
				continue;

			if ($rdi->isFile() && preg_match($exts, $rdi->getFilename()))
				$this->files[] = $rdi->getPathname();
			elseif ($rdi->isDir() && !preg_match('#^\.(svn|cvs)$#i', $rdi->getFilename()) && $rdi->hasChildren())
				$this->getFiles($rdi->getChildren());
		}
	}


}