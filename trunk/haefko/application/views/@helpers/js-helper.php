<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Application
 * @subpackage  View
 */


class JsHelper extends Object
{


	/** @var string */
	protected $code;

	/** @var string */
	protected $link;


	/**
	 * Returns new instance
	 * @return  JsHeleper
	 */
	public static function get()
	{
		return new JsHelper();
	}


	/**
	 * Adds raw javasript code
	 * @param   string    raw code
	 * @return  JsHelper  $this
	 */
	public function raw($code)
	{
		if ($this->link == '.')
			$this->code .= ";\n";

		$this->code .= "$code\n";
		$this->link = '';
		return $this;
	}


	/**
	 * Jquery selector
	 * @param   string    selector
	 * @return  JsHelper  $this
	 */
	public function jquery($selector)
	{
		if ($this->link == '.')
			$this->code .= ";\n";

		$this->code .= "jQuery('$selector')";
		$this->link = '.';
		return $this;
	}


	/**
	 * Magic method
	 * @param   string    method name
	 * @param   array     arguments
	 * @return  JsHelper  $this
	 */
	public function __call($name, $args)
	{
		foreach ($args as $i => $arg)
			$args[$i] = json_encode($arg);

		$this->code .= $this->link . $name . '(' . implode(', ', $args) . ')';
		$this->link = '.';
		return $this;
	}


	/**
	 * Renders javascript calls
	 * @param   bool      render on ready block?
	 * @return  string
	 */
	public function render($onready = true)
	{
		if (!$onready)
			return $this->code;

		return "\t<script type=\"text/javascript\">\n\t//<![CDATA[\n"
		     . "\t$(document).ready(function() {\n\t\t\n{$this->code};\n\t});\n"
		     . "\t//]]>\n\t</script>";
	}


	/**
	 * Returns javascript expressions
	 * @return string
	 */
	public function __toString()
	{
		return $this->code;
	}


}