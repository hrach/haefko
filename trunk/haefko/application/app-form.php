<?php


require_once dirname(__FILE__) . '/../libs/form.php';


class AppForm extends Form
{


	public function __construct($url, $method = 'post', $name = 'form')
	{
		$url = call_user_func_array(array(Controller::i(), 'url'), (array) $url);
		return parent::__construct($url, $method, $name);
	}

}