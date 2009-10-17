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
 */


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
	 */
	private function __construct() {}


	/**
	 * Initializes debuging, registers handlers
	 * @param bool $active active debuging
	 */
	public static function init($active = false)
	{
		if ($active)
			self::$active = true;

		static $init = false;
		if (!$init) {
			self::$isFirebug = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP/');

			set_error_handler('Debug::errorHandler');
			set_exception_handler('Debug::exceptionHandler');
			register_shutdown_function('Debug::shutdownHandler');

			if (function_exists('ini_set')) {
				ini_set('log_errors', false);
				ini_set('display_errors', false);
			}

			$init = true;
		}
	}


	/**
	 * Exception handler
	 * Catchs exception and show detail informations
	 * @param Exception $exception
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
	 * @return float
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
	 * @param Exception
	 */
	public static function renderToolbar()
	{
		if (Config::read('core.debug') > 2 || self::$active) {
			self::toolbar('Rendering time: ' . self::getTime() . 'ms');

			if (!empty(self::$toolbar))
				require_once dirname(__FILE__) . '/debug.toolbar.phtml';
		}
	}


	/**
	 * Error handler
	 * Catchs errors and show detail informations
	 * @param int $id error code
	 * @param string $message error message
	 * @param string $file error file
	 * @param int $line error line
	 * @param array $vars var content
	 */
	public static function errorHandler($id, $message, $file, $line, $vars)
	{
		if (!($id & error_reporting()))
			return;

		$render = false;
		foreach (headers_list() as $header) {
			if (stripos($header, 'content-type:') === 0) {
				if (substr($header, 14, 9) === 'text/html') {
					$render = true;
					break;
				}
			}
		}

		if ($render === true && ((class_exists('Config', false) && Config::read('core.debug') > 0) || self::$active))
			echo '<div class="error"><strong>' . self::getErrorLabel($id) . ":</strong> $message<br/>"
			    ."<strong>$file</strong> on line $line</div><br />";
		else
			self::log(self::getErrorLabel($id) . ": $message ($file on line $line)");
	}


	/**
	 * Shutdown handler
	 * Catchs fatal errors during shuting down the script
	 */
	public static function shutdownHandler()
	{
		$error = error_get_last();
		if (isset($error['type']) && $error['type'] & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE)) {
			if ((class_exists('Config', false) && Config::read('core.debug') > 0) || self::$active) {
				self::showException(new FatalErrorException($error));
			} else {
				self::log(strip_tags($error['message']) . " - $error[file] on line $error[line]");
				ob_clean();
				echo "<strong>Uncatchable application exception!</strong>\n<br /><span style='font-size:small'>"
				   . "Please contact server administrator. The error has been logged.</span>";
				exit(1);
			}
		}


		foreach (headers_list() as $header) {
			if (stripos($header, 'content-type:') === 0) {
				if (substr($header, 14, 9) === 'text/html')
					break;
				return;
			}
		}

		self::renderToolbar();
	}


	/**
	 * Returns error name by error code
	 * @param int $id error code
	 * @return string
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
	 * @param mixed $var
	 * @return mixed
	 */
	public static function dump($var)
	{
		echo '<pre style="text-align: left;">' . htmlspecialchars(print_r($var, true)) . "</pre>\n";
		return $var;
	}


	/**
	 * Debugs to debug toolbar / firebug
	 * @param string $message message
	 * @param string $group group name
	 */
	public static function toolbar($message, $group = '')
	{
		if (!(Config::read('core.debug') > 2 || self::$active))
			return false;

		# redirect content to firebug
		if ((Config::read('debug.logto') == 'firebug' || Http::$request->isAjax) && self::$isFirebug)
			return self::fireSend(is_string($message) ? strip_tags($message) : $message);

		self::$toolbar[$group][] = $message;
		return true;
	}


	/**
	 * Logs to the errorlog
	 * @param string $message message
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
	 * @param array $content content
	 * @param string $type message type (log|error)
	 * @param string $label label
	 * @throws Exception
	 * @return bool
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


	/**
	 * Displays exception errro page
	 * @param Exception
	 * @return void
	 */
	public static function showException($exception)
	{
		require_once dirname(__FILE__) . '/tools.php';
		$rendered = ob_get_contents();
		@ob_clean(); # necessary
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
function dump($var)
{
	$args = func_get_args();
	return Debug::dump($var);
}


class FatalErrorException extends Exception
{

	/** @var array */
	protected $error;


	/**
	 * Contructor
	 * @param array
	 * @return FatalErrorException
	 */
	public function __construct($error)
	{
		$this->error = $error;
		parent::__construct(ucfirst($error['message']) . ' in file "' . basename($error['file']) . '".');
	}


	/**
	 * Returns trace array for fatal error
	 * @return array
	 */
	public function getFatalTrace()
	{
		return array(array(
			'line' => $this->error['line'],
			'file' => $this->error['file'],
		));
	}


	/**
	 * Returns error tile
	 * @param string
	 */
	public function getErrorTitle()
	{
		$errors = array(
			64 => 'COMPILE ERROR',
			16 => 'CORE ERROR',
			4 => 'PARSE ERROR',
			1 => 'ERROR'
		);

		if (isset($errors[$this->error['type']]))
			return $errors[$this->error['type']];

		return "Unknown error";
	}


}
