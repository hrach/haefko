<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Libs
 */


require_once dirname(__FILE__) . '/tools.php';


class Http
{


	/** @var string */
	public static $domain;

	/** @var string */
	public static $serverUri;

	/** @var string */
	public static $baseUri;


	/**
	 * Sanitizes superglobal variables ($_GET, $_POST, $_COOKIE a $_REQUEST)
	 * @return  void
	 */
	public static function sanitizeData()
	{
		if (get_magic_quotes_gpc()) {
			$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
			while (list($key, $val) = each($process)) {
				foreach ($val as $k => $v) {
					unset($process[$key][$k]);
					if (is_array($v)) {
						$process[$key][$k] = $v;
						$process[] = & $process[$key][$k];
					} else {
						$process[$key][$k] = stripslashes($v);
					}
				}
			}
			unset($process);
		}
	}


	/**
	 * Is request by AJAX?
	 * @return  bool
	 */
	public static function isAjax()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
			return true;

		return false;
	}


	/**
	 * Returns user IP
	 * @return  string
	 */
	public static function getIp()
	{
		return $_SERVER['REMOTE_ADDR'];
	}


	/**
	 * Returns request method
	 * @return  string
	 */
	public static function getRequestMethod()
	{
		return strtolower($_SERVER['REQUEST_METHOD']);
	}


	/**
	 * Returns request url
	 * @return  string
	 */
	public static function getRequest()
	{
		$url = urldecode($_SERVER['REQUEST_URI']);
		$url = Tools::lTrim($url, dirname($_SERVER['SCRIPT_NAME']));
		$url = Tools::lTrim($url, '/' . basename($_SERVER['SCRIPT_NAME']));

		return trim($url, '/\\');
	}


	/**
	 * Sends redirect header
	 * @param   string  absolute url
	 * @param   int     redirect code
	 * @return  void
	 */
	public static function headerRedirect($url, $code = 300)
	{
		self::checkHeaders();
		header("Location: $url", true, $code);
	}


	/**
	 * Sends mime-type header
	 * @param   string  mime-type
	 * @return  void
	 */
	public static function headerMimetype($mime)
	{
		self::checkHeaders();
		header("Content-type: $mime");
	}


	/**
	 * Sends error header
	 * @param   int    error code
	 * @return  void
	 */
	public static function headerError($code = 404)
	{
		self::checkHeaders();
		switch ($code) {
		case 401:
			header('HTTP/1.1 401 Unauthorized');
			break;
		case 404:
			header('HTTP/1.1 404 Not Found');
			break;
		case 500:
			header('HTTP/1.1 500 Internal Server Error');
			break;
		default:
			throw new Exception("Unsupported error code '$code'.");
			break;
		}
	}


	/**
	 * Initialize
	 * @return void
	 */
	public static function initialize()
	{
		self::sanitizeData();

		self::$domain = $_SERVER['SERVER_NAME'];
		self::$serverUri = 'http' . (@$_SERVER['HTTPS'] ? 's' : '') . '://' . self::$domain;

		$base = trim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
		if (!empty($base))
			self::$baseUri = "/$base";
	}


	/**
	 * Checks headers
	 * @return  void
	 */
	private static function checkHeaders()
	{
		$file = $line = null;
		if (headers_sent($file, $line))
			throw new Exception("Headers has been already sent in '$file' on the line $line.");
	}


}


Http::initialize();