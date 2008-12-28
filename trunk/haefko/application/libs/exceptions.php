<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko_Application
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
	 * @param   string    error view
	 * @param   string    is view debugable?
	 * @param   int|null  error code
	 * @return  void
	 */
	public function __construct($view, $debug, $erroCode = 404)
	{
		Application::$error = true;

		if ($debug === true && Config::read('Core.debug') == 0)
			$view = '404';

		if ($errorCode !== null)
			Http::headerError(errorCode);

		$this->view = $view;
		parent::__construct("Application error: $view.");
	}


}