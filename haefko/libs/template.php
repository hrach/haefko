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
require_once dirname(__FILE__) . '/itemplate.php';


class Template extends Object implements ITemplate
{


	/** @var string */
	protected $file;

	/** @var array */
	protected $vars = array();

	/** @var array */
	protected $helpers = array();


	/**
	 * Constructor
	 * @param   string   template file
	 * @return  Template
	 */
	public function __construct($file = null)
	{
		$this->setVar('escape', 'htmlSpecialChars');
		if (!empty($file))
			$this->setFile($file);
	}


	/**
	 * Loads heleper
	 * @param   string    helper name
	 * @param   string    var name
	 * @return  Helper
	 */
	public function getHelper($name, $var = null)
	{
		static $pairs = array();

		if (!array_key_exists($name, $pairs) || $pairs[$name] != $var) {
			if (empty($var))
				$var = strtolower($name);

			$class = Inflector::helperClass($name);
			Application::get()->loadClass('helper', $class);
			$pairs[$name] = $var;
			$this->helpers[$var] = new $class;
		}

		return $this->helpers[$var];
	}


	/**
	 * Sets variable
	 * @param   string    var name
	 * @param   mixed     value
	 * @return  Template  $this
	 */
	public function setVar($key, $val)
	{
		if (empty($key))
			throw new BadMethodCallException('Key must not be empty.');

		$this->vars[$key] = $val;
		return $this;
	}


	/**
	 * Returns variable
	 * @param   string    var name
	 * @return  mixed
	 */
	public function getVar($key)
	{
		if (empty($key))
			throw new BadMethodCallException('Key must not be empty.');

		if (array_key_exists($key, $this->vars))
			return $this->vars[$key];
		
		return null;
	}


	/**
	 * Sets variables
	 * @param   array     variables
	 * @return  Template  $this
	 */
	public function setVars($vars)
	{
		$this->vars = array_merge($this->vars, (array) $vars);
		return $this;
	}

	
	/**
	 * Returns variables
	 * @return  array
	 */
	public function getVars()
	{
		return $this->vars;
	}


	/**
	 * Sets file name
	 * @param   string     filename
	 * @return  Template   $this
	 */
	public function setFile($file)
	{
		if (!file_exists($file))
			throw new Exception("Template file '$file' was not found.");

		$this->file = $file;
	}


	/**
	 * Returns file name
	 * @param  string
	 */
	public function getFile()
	{
		return $this->file;
	}


	/**
	 * Includes templatefile
	 * @param   string    filename
	 * @return  string
	 */
	public function load($file)
	{
		$template = clone $this;
		$template->setFile($file);
		$template->setVars($this->getVars());
		
		return $template->render();
	}


	/**
	 * Renders template a return content
	 * @return  string
	 */
    public function render($skipPrerender = false)
    {
		extract($this->vars);
		extract($this->helpers);

		if (class_exists('Application', false)) {
			$controller = Controller::get();
			$application = Application::get();
		}

		if (!$skipPrerender) {
			$pre = ob_get_contents();
			ob_clean();
		}

		if (!file_exists($this->file))
			throw new Exception("Template file '{$this->file}' was not found.");
			
		include $this->file;
		$return = ob_get_contents();
		ob_clean();
		
		if (!$skipPrerender)
			echo $pre;

		return $return;
	}


	/**
	 * Checks whether the variable is set
	 * @param   string    var name
	 * @return  boll
	 */
	public function __isset($name)
	{
		return isset($this->vars[$name]);
	}


	/**
	 * Unsets variable value
	 * @param   string    var name
	 * @return  void
	 */
	public function __unset($name)
	{
		unset($this->vars[$name]);
	}


	/**
	 * Sets variable value
	 * @param   string    var name
	 * @param   mixed     var value
	 * @return  void
	 */
	public function __set($name, $value)
	{
		$this->setVar($name, $value);
	}


	/**
	 * Returns variable value
	 * @param   string    var name
	 * @return  mixed
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->vars))
			return $this->vars[$name];
		else
			parent::__get($name);
	}


}