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


class ApplicationException extends Exception
{


	/** @var string */
	public $error;

	/** @var mixed */
	public $variable;


	/**
	 * Constructor
	 * @param   string  error type
	 * @param   string  view variable
	 * @return  void
	 */
	public function __construct($error, $variable = null)
	{
		static $errors = array('routing', 'missing-controller', 'missing-method', 'missing-view', 'missing-helper', 'missing-file');
		if (!in_array($error, $errors))
			throw new Exception("Unsupported ApplicationException type '$error'.");

		Application::$error = true;

		$this->error = $error;
		$this->variable = $variable;
		parent::__construct("$error: $variable");
	}


}


class ApplicationError extends Exception
{


	/** @var string ErrorView */
	public $view;


	/**
	 * Constructor
	 * @param   string  error view
	 * @param   string  is view debugable?
	 * @return  void
	 */
	public function __construct($view, $debug)
	{
		Application::$error = true;

		if ($debug === true && Config::read('Core.debug') == 0)
			$this->view = '404';
		else
			$this->view = $view;

		parent::__construct("Application error: $view.");
	}


}