<?php

/**
 * Haefko - your php5 framework
 *
 * @name 
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
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
		if (empty(self::$self))
			throw new Exception ('Application hasn\'t been alerady created.');

		return self::$self;
	}




	/** @var string */
	private $path;

	/** @var string */
	private $corePath;

	/** @var Router */
	private $router;

	/** @var Cache */
	private $cache;

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

		header('X-Powered-By: Haefko/0.8');
		$this->path = rtrim(dirname($_SERVER['SCRIPT_FILENAME']) . $path, '/');
		$this->corePath = rtrim(dirname(__FILE__), '/');
		spl_autoload_register(array($this, 'autoloadHandler'));
		set_exception_handler(array($this, 'exceptionHandler'));


		if ($config !== false)
			$this->loadConfig($this->path . $config);


		$this->router = new Router();
		$this->cache = new Cache(true, Config::read('Cache.store', $this->path . '/temp/cache/'));
	}


	/**
	 * Desctructor
	 * @return  void
	 */
	public function __destruct()
	{
		$this->terminate();
	}


	/**
	 * Prints debuggin information
	 * @return  void
	 */
	public function terminate()
	{
		# debug full time
		Debug::toolbar('Rendering time: ' . Debug::getTime() . 'ms');

		# render toolbar
		Debug::renderToolbar();
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
		ini_set('error_log', Config::read('Debug.log', $this->path . '/temp/errors.log'));

		switch (Config::read('Core.debug', 0)) {
		case 0:
			$this->loadCore('debug');
			$this->cache->lifeTime = 60*60*24; # one day
			ini_set('log_errors', true);
			ini_set('display_errors', false);
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
			break;
		case 1:
			$this->loadCore('debug');
			$this->cache->lifeTime = 60*60; # one hour
			ini_set('log_errors', false);
			ini_set('display_errors', true);
			error_reporting(E_ERROR | E_PARSE);
			break;
		case 2:
			$this->loadCore('debug');
			$this->cache->lifeTime = 60*5; # five minutes
			ini_set('log_errors', false);
			ini_set('display_errors', true);
			error_reporting(E_ALL);
			break;
		default: # other levels
			$this->loadCore('debug');
			$this->cache->lifeTime = 30; # thirty seconds
			ini_set('log_errors', false);
			ini_set('display_errors', true);
			error_reporting(E_ALL);
			break;
		}

		if (Config::read('Cache.enabled', true))
			$this->cache->enabled = true;
		else
			$this->cache->enabled = false;
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
		$this->loadFile($file);

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
		$autoload = new Autoload($this->cache);
		$autoload->exts = Config::read('Autoload.exts', $autoload->exts);
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
		$this->loadAppController();
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
		# ajax support
		if (isset($this->contorller) && $this->controller->isAjax) {
			if (Config::read('Core.debug') == 0)
				echo json_encode(array('response' => 'Internal server error.'));
			else
				echo json_encode(array('response' => $exception->getMessage()));

			exit;
		}

		try {

			self::$error = true;
			# render application layout
			if ($exception instanceof ApplicationException || Config::read('Core.debug') == 0) {

				$this->loadAppController();
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
					$this->controller->view->variable = $exception->variable;
					$this->controller->view->message = $exception->getMessage();
				}

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
				die("<strong>Uncatchable application exception!</strong>"
				  . "<br /><span style='font-size:small'>"
				  . "Please contact server administrator. The error has been logged.</span>");
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
		static $libs = array('autoload', 'cookie', 'session', 'debug', 'html', 'l10n', 'form',
		                     'db', 'db-table', 'db-table-structure');

		$ci_class = strtolower($class);
		if (in_array($ci_class, $libs))
			$this->loadCore($class, false);
		elseif (class_exists('DbTableStructure', false) && DbTableStructure::get()->existTable($class))
			eval("class $class extends DbTable {} $class::\$table = '" . Tools::underscore($class) . "';");
		elseif (Tools::endWith($ci_class, 'controller'))
			$this->loadClass('controller', $class);
		elseif (Tools::endWith($ci_class, 'helper'))
			$this->loadClass('helper', $class);
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
	 * Returns Router
	 * @return  Router
	 */
	public function getRouter()
	{
		return $this->router;
	}


	/**
	 * Returns Cache
	 * @return  Cache
	 */
	public function getCache()
	{
		return $this->cache;
	}


	/**
	 * Loads or creates AppController
	 * @return  void
	 */
	private function loadAppController()
	{
		if (class_exists('AppController', false))
			return true;

		try {
			$this->loadClass('controller', 'AppController');
		} catch (ApplicationException $e) {
			eval('class AppController extends Controller {}');
		}
	}


}