<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.8
 * @package     Haefko
 */



/**
 * Vyjimka aplikace
 */
class ApplicationException extends Exception
{

	/** @var string */
	public $error;



	/**
	 * Konstruktor
	 * @param   string  typ chyby (routing|missing-controller|missing-method|missing-view|missing-helper|missing-file)
	 * @param   string  promenna pro view
	 * @return  void
	 */
	public function __construct($error, $message = null)
	{
		static $errors = array('routing', 'missing-controller', 'missing-method', 'missing-view', 'missing-helper', 'missing-file');

		if (!in_array($error, $errors))
			die("Exception: nepodporovany typ vyjimky '$error'.");

		Application::$error = true;

		$this->error = $error;
		parent::__construct($message);
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
	}


}