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


class Helper
{


	/** @var CustomController */
	protected $controller;


	/**
	 * Constructor
	 * @return  void
	 */
	public function __construct()
	{
		$this->controller = Controller::i();
	}


}