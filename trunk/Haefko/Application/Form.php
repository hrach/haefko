<?php




class Form extends BasicForm
{


	/**
	 * Constructor
	 * @param   string  url
	 * @param   boole   internal url?
	 * @param   string  name
	 * @return  string  form name
	 */
	public function __construct($url = null, $internalUrl = true, $name = 'form')
	{
		if ($internalUrl === true)
			$url = Application::getInstance()->controller->url($url);
	}


}