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


require_once dirname(__FILE__) . '/../../libs/form.php';


class AppForm extends Form
{


	/**
	 * Constructor
	 * Url is proccessed by Controller::url()
	 * @param   string  url
	 * @param   string  method
	 * @param   string  form name
	 * @return  string  form name
	 */
	public function __construct($url, $method = 'post', $name = 'form')
	{
		$url = call_user_func_array(array(Controller::i(), 'url'), (array) $url);
		return parent::__construct($url, $method, $name);
	}


}