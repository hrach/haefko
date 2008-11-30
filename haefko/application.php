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
 */


ob_start();
$startTime = microtime(true);


require_once dirname(__FILE__) . '/libs/tools.php';
require_once dirname(__FILE__) . '/libs/http.php';
require_once dirname(__FILE__) . '/libs/object.php';
require_once dirname(__FILE__) . '/libs/cache.php';
require_once dirname(__FILE__) . '/application/libs/exceptions.php';
require_once dirname(__FILE__) . '/application/libs/inflector.php';
require_once dirname(__FILE__) . '/application/libs/router.php';
require_once dirname(__FILE__) . '/application/libs/config.php';




class Application extends Object
{


	/** @var bool Error mode */
	public static $error = false;

	/** @var Application */
	private static $self;


	/**
	 * Returns instance of Application
	 * @return  Application
	 */
	public static function get()
	{
		return self::$self;
	}




	/** @var string */
	private $path;

	/** @var string */
	private $corePath;

	/** @var Router */
	private $router;

	/** @var CustomController */
	private $controller;


	/**
	 * Application constructor
	 * @param   string         application path
	 * @param   string|false   configuration file|don't load default
	 * @return  void
	 */
	public function __construct($path = '/app', $config = '/config.yml')
	{
		self::$self = & $this;

		$this->path = rtrim(dirname($_SERVER['SCRIPT_FILENAME']) . $path, '/');
		$this->corePath = rtrim(dirname(__FILE__), '/');
		spl_autoload_register(array($this, 'autoloadHandler'));
		set_exception_handler(array($this, 'exceptionHandler'));

		if ($config !== false)
			$this->loadConfig($this->path . $config);

		$this->router = new Router();
	}


	/**
	 * Desctructor
	 * @return  void
	 */
	public function __destruct()
	{
		# debug sqls
		if (Config::read('Core.debug') >= 2) {
			Debug::fireLog('Sqls', 'GROUP_START');
			foreach (Db::$sqls as $sql)
				Debug::fireLog($sql, 'LOG');
			Debug::fireLog('Sqls', 'GROUP_END');
		}

		# debug full time
		if (Config::read('Core.debug') >= 1) {
			Debug::fireLog(Debug::getTime() . 'ms', 'INFO', 'Rendering time');
		}
	}


	/**
	 * Parses file configuration and load the configuration
	 * @param   string  filename
	 * @throws  Exception
	 * @return  void
	 */
	public function loadConfig($file)
	{
		if (!is_file($file))
			throw new Exception('Missing configuration file ' . Tools::relativePath($file) . '.');

		Config::multiWrite(Config::parseFile($file));

		$this->setDebugMode();
		Cache::$store = Config::read('Cache.store', $this->path . '/temp/cache/');
		Cache::$enabled = true;
	}


	/**
	 * Loads framework core file
	 * @param   string  filename
	 * @param   bool    is application core-file?
	 * @throws  Exception
	 * @return  void
	 */
	public function loadCore($file)
	{
		$file = Tools::dash($file);
		$file = dirname(__FILE__) . "/libs/$file.php";

		if (!file_exists($file))
			throw new Exception('Missing core file \'' . Tools::relativePath($file) . '\'.');

		require_once $file;
	}


	/**
	 * Loads framework file
	 * Tries load file from appliacation path or framework core path
	 * @param   string  filename
	 * @throws  ApplicationException
	 * @return  void
	 */
	public function loadFile($file)
	{
		$file1 = $this->path . "/$file";
		$file2 = dirname(__FILE__) . "/application/$file";

		if (file_exists($file1))
			return require_once $file1;
		elseif (file_exists($file2))
			return require_once $file2;

		throw new ApplicationException('missing-file', $file);
	}


	/**
	 * Loads framework class
	 * @param   string  class type
	 * @param   string  class name
	 * @throws  Exception|ApplicationException
	 * @return  void
	 */
	public function loadClass($type, $class)
	{
		if (!in_array($type, array('controller', 'helper')))
			throw new Exception("Unsupported class-type '$type'.");


		$file = call_user_func_array(array('Inflector', "{$type}File"), array($class));
		$this->loadFile($file, true);

		if (!class_exists($class, false))
			throw new ApplicationException('missing-' . $type, $class);
	}


	/**
	 * Activates autoload for "/app/extends" and others
	 * @param   array      directories for autoload
	 * @return  Autoload
	 */
	public function autoload($dirs = array())
	{
		$autoload = new Autoload();
		$autoload->exts = Config::read('Autoload.exts', $autoload->exts);
		$autoload->cache = Config::read('Autoload.cache', "{$this->path}/temp/cache/{$autoload->cache}");
		$autoload->autoRebuild = Config::read('Core.debug') > 1;

		array_unshift($dirs, "{$this->path}/extends/");
		foreach ((array) $dirs as $dir)
			$autoload->addDir($dir);

		return $autoload->load();
	}


	/**
	 * Runs the application
	 * @throws  ApplicationException
	 * @return  void
	 */
	public function run()
	{
		try {
			$this->loadClass('controller', 'AppController');
		} catch (ApplicationException $e) {
			eval('class AppController extends Controller {}');
		}

		if ($this->router->routed === false)
			throw new ApplicationException('routing');

		$class = Inflector::controllerClass($this->router->controller, $this->router->module);
		$this->loadClass('controller', $class);

		$this->controller = new $class;
		echo $this->controller->render();
	}


	/**
	 * Catchs all exceptions and renders error page/message
	 * @param   Exception
	 * @return  void
	 */
	public function exceptionHandler(Exception $exception)
	{
		try {

			# render application layout
			if ($exception instanceof ApplicationException || Config::read('Core.debug') == 0) {

				//$this->router->service = null;
				$this->controller = new AppController();

				if (Config::read('Core.debug') == 0) {
					if ($exception instanceof ApplicationException) {
						$this->controller->view->view('404');
					} else {
						$this->controller->view->view('500');
						Debug::log($exception->getMessage());
					}
				} else {
					$this->controller->view->view($exception->error);
					$this->controller->view->message = $exception->getMessage();
				}

				$this->controller->view->loadHelpers();
				$this->controller->init();
				echo $this->controller->view->render();

			# render debug template
			} else {
				Debug::exceptionHandler($exception);
			}

		# render temporary message
		} catch (Exception $e) {
			if (Config::read('Core.debug') == 0) {
				Debug::log($e->getMessage());
				die("<strong>Uncatchable application excetion!</strong>"
				  . "<br /><span style='font-size:small'>"
				  . "Plesase, contact the server administrator. Error was logged.</span>");
			} else {
				Debug::exceptionHandler($e);
			}
		}
	}


	/**
	 * Application autoload handler
	 * @param   string  class name
	 * @return  void
	 */
	public function autoloadHandler($class)
	{
		static $libs = array('autoload', 'cookie', 'session', 'debug', 'html', 'l10n',
		                     'db', 'db-table', 'db-table-structure');

		$ci_class = strtolower($class);
		if (in_array($ci_class, $libs))
			$this->loadCore($class, false);
		elseif (class_exists('DbTableStructure', false) && DbTableStructure::existTable($class))
			eval("class $class extends DbTable {} $class::\$table = '" . Tools::underscore($class) . "';");
		elseif (Tools::endWith($ci_class, 'controller'))
			$this->loadClass('controller', $class);
		elseif (Tools::endWith($ci_class, 'helper'))
			$this->loadClass('helper', $class);

		if (method_exists($class, 'initConfig'))
			call_user_func(array($class, 'initConfig'));
	}


	/**
	 * Sets debug configuration
	 * @return  void
	 */
	private function setDebugMode()
	{
		ini_set('error_log', Config::read('Core.log', "{$this->path}/temp/errors.log"));

		if (Config::read('Core.debug') > 0) {
			ini_set('display_errors', true);
			ini_set('error_reporting', E_ALL);
			ini_set('log_errors', false);
		} else {
			ini_set('display_errors', false);
			ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE);
			ini_set('log_errors', true);
		}
	}


	/**
	 * Returns controller
	 * @return  Controller
	 */
	public function getController()
	{
		return $this->controller;
	}


	/**
	 * Returns application path
	 * @return  string
	 */
	public function getPath()
	{
		return $this->path;
	}


	/**
	 * Returns framework core path
	 * @return  string
	 */
	public function getCorePath()
	{
		return $this->corePath;
	}


	/**
	 * Returns framework core path
	 * @return  string
	 */
	public function getRouter()
	{
		return $this->router;
	}


}