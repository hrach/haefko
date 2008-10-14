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



class Debug
{


	/**
	 * Return time in miliseconds
	 * @return  float
	 */
	public static function getTime()
	{
		global $startTime;
		return round((microtime(true) - $startTime) * 1000, 2);
	}



	/**
	 * Vytvori specialni debug listu s informace o prave probehlem rizeni aplikace
	 * @return  void
	 */
	public static function debugToolbar()
	{
		//$sql = self::$sqls;
		require_once dirname(__FILE__) . '/debug.toolbar.phtml';
	}


	/**
	 * Vypise lidsky-citelny obsah a strukturu promenne
	 * @param   mixed   promenna pro vypis
	 * @return  void
	 */
	public static function dump($var)
	{
		echo '<pre style="text-align: left;">' . htmlspecialchars(print_r($var, true)) . '</pre>';
	}



	/**
	 * Zachyti vyjimky a zobrazi podrobny debug vypis
	 * @param   Exception   nezachycena vyjimka
	 * @return  void
	 */
	public static function exceptionHandler(Exception $exception)
	{
		//@ob_clean();
		require_once dirname(__FILE__) . '/debug.exception.phtml';
	}



	public static function queryHandler($query)
	{
		self::$sqls[] = array(
			'sql' => $query['sql'],
			'time' => '',
			'rows' => $query['result']->affectedRows()
		);
	}



	/**
	 * Prevede pole do html reprezentace
	 * @param   array   pole pro prevod
	 * @return  string
	 */
	public static function readableArray($array, $indent = 0)
	{
		$ret = null;
		$tab = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $indent);

		foreach ($array as $key => $val) {
			if (preg_match('#(pass(ord)?|passwd?|pin|cc)#i', $key))
				continue;

			$ret .= "$tab$key: ";

			if (is_array($val))
				$ret .= "<br />" . self::readableArray($val, $indent + 1);
			else
				$ret .= "<strong>$val</strong><br />";
		}

		return $ret;
	}



}