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


require_once dirname(__FILE__) . '/object.php';
require_once dirname(__FILE__) . '/cache.php';


class Autoload extends Object
{


	/** @var array Allowed extension */
	public $exts = array('php');

	/** @var bool */
	public $autoRebuild = false;

	/** @var bool */
	public $rebuild = false;

	/** @var array */
	private $classes = array();

	/** @var array */
	private $files = array();

	/** @var array */
	private $dirs = array();

	/** @var Cache */
	private $cache;


	/**
	 * Contructor
	 * Registers autoload
	 * @param   string|Cache    cache path or Cache instance
	 * @return  void
	 */
	public function __construct($store = './')
	{
		if ($store instanceof Cache)
			$this->cache = $store;
		else
			$this->cache = new Cache(true, $store, false);

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

		$this->cache->write('autoload', 'classes', $this->classes);
		return $this;
	}


	/**
	 * Loads list of cached classes or create it
	 * @return  Autoload
	 */
	public function load()
	{
		$this->classes = $this->cache->read('autoload', 'classes', false);
		if ($this->classes === null)
			$this->rebuild();

		return $this;
	}


	/**
	 * Returns list of classes
	 * @return  array
	 */
	public function getClasses()
	{
		return $this->classes;
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