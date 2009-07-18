<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
 * @subpackage  Templates
 */


require_once dirname(__FILE__) . '/../tools.php';


class FilterHelper extends Object
{


	/**
	 * Constructor
	 * @param Template $template
	 * @param string $varName
	 * @return FilterHelper
	 */
	public function __construct(Template $template = null, $varName = null)
	{
		if ($template) {
			if (empty($varName))
				$varName = 'filter';

			static $filters = array('escape', 'trim', 'lower', 'upper', 'nl2br',
				'strip', 'stripTags', 'date', 'format', 'shuffle', 'explode',
				'implode');

			foreach ($filters as $f)
				$template->tplFilters[$f] = "\${$varName}->$f";
		}
	}


	/**
	 * Escape filter
	 * @param string $var
	 * @param string $mode escaping mode - html/js/css
	 * @return string
	 */
	public function escape($var, $mode = 'html')
	{
		switch ($mode) {
			case 'js':
			case 'javascript':
				$var = str_replace(']]>', ']]\x3E', json_encode($s));
				return htmlspecialchars($var, ENT_QUOTES);
			case 'css':
				$var = addcslashes($s, "\x00..\x2C./:;<=>?@[\\]^`{|}~");
				return htmlspecialchars($var, ENT_QUOTES);
			case 'html':
			case 'xhtml':
			default:
				return htmlspecialchars($var, ENT_QUOTES);
		}
	}


	/**
	 * Trim filter
	 * @param string $var
	 * @param string $chars
	 * @return string
	 */
	public function trim($var, $chars = null)
	{
		return trim($vars, $chars);
	}


	/**
	 * Lower (strtolower) filter
	 * @param string $var
	 * @return string
	 */
	public function lower($var)
	{
		return strtolower($var);
	}


	/**
	 * Upper (strtoupper) filter
	 * @param string $var
	 * @return string
	 */
	public function upper($var)
	{
		return strtoupper($var);
	}


	/**
	 * NL to BR filter
	 * @param string $var
	 * @return string
	 */
	public function nl2br($var)
	{
		return nl2br($var);
	}


	/**
	 * Strip filter - strips (white) spaces
	 * @param string $var
	 * @param string $replace
	 * @return string
	 */
	public function strip($var, $replace = ' ')
	{
		return preg_replace('#\s*#', $replace, $var);
	}


	/**
	 * Strip tags filter
	 * @param string $var
	 * @return string
	 */
	public function stripTags($var)
	{
		return strip_tags($var);
	}


	/**
	 * Date filter
	 * @param string $var
	 * @param string $format
	 * @return string
	 */
	public function date($var, $format = 'd.m.Y')
	{
		return date($format, $var);
	}


	/**
	 * Format (sprintf) filter
	 * @param string $var
	 * @param string $format
	 * @return string
	 */
	public function format($var, $format)
	{
		$args = func_get_args();
		return call_user_func_array('sprintf', $args);
	}


	/**
	 * Shuffle filter
	 * @param array $var
	 * @return array
	 */
	public function shuffle($var)
	{
		shuffle($var);
		return $var;
	}


	/**
	 * Explode filter
	 * @param string $var
	 * @param string $delimeter
	 * @return array
	 */
	public function explode($var, $delimeter = ', ')
	{
		return explode($delimeter, $var);
	}


	/**
	 * Implode filter
	 * @param array $var
	 * @param string $delitemter
	 * @return string
	 */
	public function implode($var, $delitemter = ', ')
	{
		return implode($delimeter, $var);
	}


}