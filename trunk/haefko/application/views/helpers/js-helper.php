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
 * @subpackage  View
 */


class JsHelper extends Object
{


	/** @var string */
	protected $js;

	/** @var string */
	private $linkBy;


	/**
	 * Prida html tagy pro include js $file
	 * @param   strgin  jmeno js souboru
	 * @return  bool
	 */
	public function js($file)
	{
		Controller::get()->view->helper('html')->addHeader(
			Controller::get()->view->helper('html')->js($file)
		);

		return true;
	}






	/**
	 * Vyrenderuje js kod pro hlavicku
	 * @return  string
	 */
	public function render()
	{
		$code = "\t<script type=\"text/javascript\">\n\t//<![CDATA[\n"
			  .  "\t$(document).ready(function() {\n\t\t\n{$this->js};\n\t});\n"
			  .  "\t//]]>\n\t</script>";

		return $code;
	}



	/**
	 * Prida surovy javasriptovy kod
	 * @param   string  kod
	 * @return  void
	 */
	public function raw($code)
	{
		if ($this->linkBy == '.')
			$this->js .= ";\n";

		$this->js .= "$code\n";
		$this->linkBy = '';
	}



	/**
	 * Selector
	 * @param   string  selector
	 * @return  JsHelper
	 */
	public function jquery($selector)
	{
		if ($this->linkBy == '.')
			$this->js .= ";\n";

		$this->js .= "$('$selector')";
		$this->linkBy = '.';

		return $this;
	}



	/**
	 * Magic method
	 * @param   string  jmeno metody
	 * @param   array   argumenty metody
	 * @return  JsHelper
	 */
	public function __call($name, $args)
	{
		foreach ($args as & $val)
			$val = json_encode($val);

		$this->js .= "{$this->linkBy}$name(" . implode(', ', $args). ")";
		$this->linkBy = '.';

		return $this;
	}


}