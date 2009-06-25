<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Libs
 */


require_once dirname(__FILE__) . '/fatal-error-exception.php';


class Debug
{


	/** @var bool */
	public static $isFirebug = false;

	/** @var string */
	public static $logFile = 'errors.log';

	/** @var array */
	private static $toolbar = array();

	/** @var bool */
	private static $active = false;


	/**
	 * Constructor
	 * @throws  Exception
	 * @return  void
	 */
	public function __construct()
	{
		throw Exception('Class Debug cannot be instance.');
	}


	/**
	 * Initializes debuging, registers handlers
	 * @param   bool   active debuggin
	 * @return  void
	 */
	public static function init($active = false)
	{
		if ($active)
			self::$active = true;

		self::$isFirebug = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP/');

		set_error_handler('Debug::errorHandler');
		set_exception_handler('Debug::exceptionHandler');
		register_shutdown_function('Debug::shutdownHandler');

		if (function_exists('ini_set')) {
			ini_set('log_errors', false);
			ini_set('display_errors', false);
		}
	}


	/**
	 * Exception handler
	 * Catchs exception and show detail informations
	 * @param   Exception
	 * @return  void
	 */
	public static function exceptionHandler(Exception $exception)
	{
		static $errMessage = "<strong>Uncatchable application exception!</strong>\n<br /><span style='font-size:small'>Please contact server administrator. The error has been logged.</span>";

		if (class_exists('Application', false)) {

			try {

				$app = Application::get();
				$app->processException($exception);

			} catch (Exception $e) {

				if (Config::read('core.debug') == 0) {
					self::log($e->getMessage());
					echo $errMessage;
				    exit(1);
				}

				self::showException($e);
			}

		} else {
		
			if ((class_exists('Config', false) && Config::read('core.debug') > 0) || self::$active) {
				self::showException($exception);
			} else {
				self::log($exception->getMessage());
				echo $errMessage;
			    exit(1);
			}

		}
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
	 * Error handler
	 * Catchs errors and show detail informations
	 * @param   int     error code
	 * @param   string  error message
	 * @param   string  error file
	 * @param   int     error line
	 * @param   array   var content
	 * @return  void
	 */
	public static function errorHandler($id, $message, $file, $line, $vars)
	{
		if (!($id & error_reporting()))
			return;

		if ((class_exists('Config', false) && Config::read('core.debug') > 0) || self::$active)
			echo '<div class="error"><strong>' . self::getErrorLabel($id) . ":</strong> $message<br/>"
			    ."<strong>$file</strong> on line $line</div><br />";
		else
			self::log(self::getErrorLabel($id) . ": $message ($file on line $line)");
	}


	/**
	 * Shutdown handler
	 * Catchs fatal errors during shuting down the script
	 * @return  void
	 */
	public static function shutdownHandler()
	{
		$error = error_get_last();
		if (empty($error))
			return;

		if (!($error['type'] & error_reporting()) || !($error['type'] & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE)))
			return;


		if (class_exists('Config', false) && Config::read('core.debug') > 0) {
			self::showException(new FatalErrorException($error));
		} else {
			self::log(strip_tags($error['message']) . " - $error[file] on line $error[line]");
			ob_clean();
			echo "<strong>Uncatchable application exception!</strong>\n<br /><span style='font-size:small'>"
			   . "Please contact server administrator. The error has been logged.</span>";
			exit(1);
		}
	}


	/**
	 * Returns error name by error code
	 * @param   int     error code
	 * @return  string
	 */
	public static function getErrorLabel($id)
	{
		static $levels = array(
			2047 => 'E_ALL',
			1024 => 'E_USER_NOTICE',
			512 => 'E_USER_WARNING',
			256 => 'E_USER_ERROR',
			128 => 'E_COMPILE_WARNING',
			64 => 'E_COMPILE_ERROR',
			32 => 'E_CORE_WARNING',
			16 => 'E_CORE_ERROR',
			8 => 'E_NOTICE',
			4 => 'E_PARSE',
			2 => 'E_WARNING',
			1 => 'E_ERROR'
		);

		if (isset($levels[$id]))
			return $levels[$id];
		else
			return "Undefined error";
	}


	/**
	 * Dumps contents and structure of variable
	 * @param   mixed
	 * @return  mixed
	 */
	public static function dump($var)
	{
		echo '<pre style="text-align: left;">' . htmlspecialchars(print_r($var, true)) . "</pre>\n";
		return $var;
	}


	/**
	 * Debugs to debug toolbar / firebug
	 * @param   string  message
	 */
	public static function toolbar($message, $group = '')
	{
		if (Config::read('core.debug') < 3)
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
		$fh = fopen(self::$logFile, 'a+');
		if (!$fh)
			die("Cannot write to error log file!");

		$dt = date('[Y-m-d H:i:s] ');
		fwrite($fh, $dt . $message ."\n");
		fclose($fh);
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
		$file = $line = null;
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

	
	public static function showException($exception) 
	{	
		require_once dirname(__FILE__) . '/tools.php';
		$rendered = ob_get_contents();
		ob_clean();
		require_once dirname(__FILE__) . '/debug.exception.phtml';
	}


}


if (!isset($startTime))
	$startTime = microtime(true);

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