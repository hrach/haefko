<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Libs
 */



class FatalErrorException extends Exception
{


	/** @var array */
	protected $error;


	/**
	 * Contructor
	 * @param   array
	 * @return  FatalErrorException
	 */
	public function __construct($error)
	{
		$this->error = $error;
		parent::__construct(ucfirst($error['message']) . ' in file "' . basename($error['file']) . '".');
	}


	/**
	 * Returns trace array for fatal error
	 * @return  array
	 */
	public function getFatalTrace()
	{
		return array(array(
			'line' => $this->error['line'],
			'file' => $this->error['file'],
		));
	}


	/**
	 * Returns error tile
	 * @param  string
	 */
	public function getErrorTitle()
	{
		$errors = array(
			64 => 'COMPILE ERROR',
			16 => 'CORE ERROR',
			4 => 'PARSE ERROR',
			1 => 'ERROR'
		);

		if (isset($errors[$this->error['type']]))
			return $errors[$this->error['type']];

		return "Unknown error";
	}


}