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
	 * Returns time in miliseconds
	 * @return  float
	 */
	public static function getTime($startTime = null)
	{
		if (empty($startTime))
			global $startTime;

		return round((microtime(true) - $startTime) * 1000, 2);
	}


	/**
	 * Logs to the errorlog
	 * @param   string  message
	 * @return  void
	 */
	public static function log($message)
	{
		error_log($message);
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


	/**
	 * Dumps variable to Firebug
	 * @param  mixed     variable
	 * @param  string    unique key
	 * @return bool
	 */
	public static function fireDump($var, $key)
	{
		return self::fireSend(2, array((string) $key => $var));
	}


	/**
	 * Sends message to Firebug
	 * @param  mixed   message to log
	 * @param  string  priority of message (LOG, INFO, WARN, ERROR, GROUP_START, GROUP_END)
	 * @param  string  optional label
	 * @return bool    was successful?
	 */
	public static function fireLog($message, $priority = self::LOG, $label = NULL)
	{
		if ($message instanceof Exception) {
			$priority = 'TRACE';
			$message = array(
				'Class' => get_class($message),
				'Message' => $message->getMessage(),
				'File' => $message->getFile(),
				'Line' => $message->getLine(),
				'Trace' => self::replaceObjects($message->getTrace()),
			);
		} elseif ($priority === 'GROUP_START') {
			$label = $message;
			$message = NULL;
		}
		return self::fireSend(1, array(array('Type' => $priority, 'Label' => $label), self::replaceObjects($message)));
	}



	/**
	 * Sends debug headers
	 * @param  int     structure's index
	 * @param  array   content
	 * @return bool
	 */
	private static function fireSend($structure, $content)
	{
		static $counter = 0;

		# cheack headers
		if (headers_sent($file))
			throw new Exception("Headers has been alerady sent. $file");


		# send headers
		header('X-Wf-Protocol-hf: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
		header('X-Wf-hf-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.2.0');
		if ($structure === 1)
			header('X-Wf-hf-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');
		else
			header('X-Wf-hf-Structure-2: http://meta.firephp.org/Wildfire/Structure/FirePHP/Dump/0.1');


		# send content
		foreach (str_split(json_encode($content), 5000) as $part)
			header("X-Wf-hf-$structure-1-n" . ++$counter .": |$part|\\");

		header("X-Wf-hf-$structure-1-n$counter: |$part|");
		header("X-Wf-hf-Index: n$counter");

		return true;
	}


	/**
	 * fireLog helper
	 * @param  mixed
	 * @return mixed
	 */
	static private function replaceObjects($val)
	{
		if (is_object($val)) {
			return 'object ' . get_class($val) . '';

		} elseif (is_array($val)) {
			foreach ($val as $k => $v) {
				unset($val[$k]);
				$val[$k] = self::replaceObjects($v);
			}
		}
		return $val;
	}



}