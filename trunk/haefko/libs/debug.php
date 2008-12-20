<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko_Libs
 */


class Debug
{


	/** @var bool */
	public static $isFirebug = false;

	/** @var array */
	private static $toolbar = array();


	/**
	 * Constructor
	 */
	public function __construct()
	{
		throw Exception('Class Debug cannot be instance.');
	}


	/**
	 * Initializes Debug
	 */
	public static function init()
	{
		self::$isFirebug = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP/');
	}


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
	 * Exception handler
	 * Catchs exception and show detail informations
	 * @param   Exception
	 * @return  void
	 */
	public static function renderToolbar()
	{
		if (Config::read('Core.debug') > 2 && !empty(self::$toolbar))
			require_once dirname(__FILE__) . '/debug.toolbar.phtml';
	}


	/**
	 * Exception handler
	 * Catchs exception and show detail informations
	 * @param   Exception
	 * @return  void
	 */
	public static function exceptionHandler(Exception $exception)
	{
		$rendered = ob_get_contents();
		ob_clean();
		require_once dirname(__FILE__) . '/debug.exception.phtml';
	}


	/**
	 * Dumps contents and structure of variable
	 * @param   mixed
	 * @return  mixed
	 */
	public static function dump($var)
	{
		echo '<pre style="text-align: left;">' . htmlspecialchars(print_r($var, true)) . '</pre>';
		return $var;
	}


	/**
	 * Prints or sends to firebug (in ajax request) debug message/variable content
	 * @param   mixed     varbiable
	 * @return  void
	 */
	public static function debug($var)
	{
		if (Config::read('Core.debug') < 2)
			return;

		if (is_array($var)) {
			$array = 'array(';
			if (array_keys($var) == range(0, count($var) - 1)) {
				foreach ($var as $val)
					$array .= "'$val', ";
			} else {
				foreach ($var as $key => $val)
					$array .= "'$key' => '$val', ";
			}
			$array .= ');';
			$var = $array;
		}

		if ((self::$isFirebug && Config::read('Core.logTo', 'toolbar') == 'firebug') || Http::isAjax())
			self::fireSend($var);
		else
			echo $var;
	}


	/**
	 * Debugs to debug toolbar / firebug
	 * @param   string  message
	 */
	public static function toolbar($message, $group = '')
	{
		if (Config::read('Core.debug') < 3)
			return false;

		# redirect content to firebug
		if (Config::read('Debug.logto') == 'firebug' && self::$isFirebug)
			return self::fireSend($message, $group);

		self::$toolbar[$group][] = $message;
		return true;
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
	 * Sends headers to firebug
	 * @param   array     content
	 * @param   string    message type (log|error)
	 * @param   string    label
	 * @throws  Exception
	 * @return  bool
	 */
	private static function fireSend($content, $type = 'log', $label = null)
	{
		if (!self::$isFirebug)
			return false;

		# cheack headers
		if (headers_sent($file, $line))
			throw new Exception("Headers has been alerady sent. ($file, $line)");


		static $counter = 0;
		header('X-Wf-Protocol-hf: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
		header('X-Wf-hf-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.2.0');
		header('X-Wf-hf-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');

		$content = array(
			array('Type' => strtolower($type), 'Label' => $label),
			$content
		);

		# send content
		$parts = str_split(json_encode($content), 500);
		$last = array_pop($parts);
		foreach ($parts as $part)
			header('X-Wf-hf-1-1-h' . ++$counter .": |$part|\\");
		header('X-Wf-hf-1-1-h' . ++$counter . ": |$last|");

		return true;
	}


}


Debug::init();


/**
 * Wrapper for Debug::dump()
 * @see Debug::dump();
 */
function dump()
{
	$args = func_get_args();
	return call_user_func_array(array('Debug', 'dump'), $args);
}


/**
 * Wrapper for Debug::debug()
 * @see Debug::debug();
 */
function debug()
{
	$args = func_get_args();
	return call_user_func_array(array('Debug', 'debug'), $args);
}