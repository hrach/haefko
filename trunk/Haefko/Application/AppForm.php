<?php


require_once dirname(__FILE__) . '/../Form.php';


class AppForm extends Form
{


	public function __construct($url, $method = 'post', $name = 'form')
	{
		$url = call_user_func_array(array(Application::getInstance()->controller, 'url'), (array) $url);
		return parent::__construct($url, $method, $name);
	}

}