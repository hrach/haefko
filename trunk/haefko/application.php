<?php

/**
 * Haefko - your php5 framework
 *
 * @name 
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Application
 */


ob_start();
$startTime = microtime(true);


require_once dirname(__FILE__) . '/libs/tools.php';
require_once dirname(__FILE__) . '/libs/object.php';
require_once dirname(__FILE__) . '/libs/http.php';
require_once dirname(__FILE__) . '/libs/debug.php';
require_once dirname(__FILE__) . '/libs/cache.php';
require_once dirname(__FILE__) . '/libs/config.php';
require_once dirname(__FILE__) . '/application/libs/exceptions.php';
require_once dirname(__FILE__) . '/application/libs/inflector.php';
require_once dirname(__FILE__) . '/application/libs/router.php';


/**
* @property-read Router $router
*/
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


	/**
	 * Returs framework version and logo
	 * @param   bool     add image?
	 * @return  string
	 */
	public static function getFrameworkInfo($image = true)
	{
		if ($image)
			return '<a href="http://haefko.skrasek.com"><img src="http://haefko.skrasek.com/media/powered.png" class="hf-powered" /></a>';
		else
			return '<a href="http://haefko.skrasek.com">Haefko 0.8.5</a>';
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


		if ($config !== false)
			Config::multiWrite(Config::parseFile($this->path . $config));


		if (Config::read('cache.storage.relative', true))
			$cachePath = $this->path . Config::read('cache.storage.path', '/temp/cache');
		else
			$cachePath = Config::read('cache.storage.path');


		$this->router = new Router();
		$this->cache = new Cache(true, $cachePath);
		$this->initConfig();
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
	 * Inits application configuraction
	 * @return  void
	 */
	public function initConfig()
	{
		Debug::$logFile = Config::read('debug.log', $this->path . '/temp/errors.log');

		switch (Config::read('core.debug', 0)) {
		case 0:
			$this->cache->lifeTime = 60*60*24; # 1 day
			error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
			break;
		case 1:
			$this->cache->lifeTime = 60*60; # 1 hour
			error_reporting(E_ALL ^ E_NOTICE);
			break;
		case 2:
			$this->cache->lifeTime = 60*5; # 5 minutes
			error_reporting(E_ALL);
			break;
		default:
			$this->cache->lifeTime = 30; # 30 seconds
			error_reporting(E_ALL);
			break;
		}

		if (Config::read('cache.enabled', true))
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
		if ($this->router->routed === false || empty($this->router->controller))
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
	public function processException(Exception $exception)
	{
		# ajax support
		if (isset($this->contorller) && $this->controller->isAjax) {
			if (Config::read('Core.debug') == 0)
				echo json_encode(array('response' => 'Internal server error.'));
			else
				echo json_encode(array('response' => $exception->getMessage()));

			exit;
		}


		if (!($exception instanceof ApplicationException || Config::read('Core.debug') == 0)) {
			Debug::showException($exception);
			exit(1);
		}


		self::$error = true;
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
		echo $this->controller->view->renderTemplates();
	}


	/**
	 * Application autoload handler
	 * @param   string  class name
	 * @return  void
	 */
	public function autoloadHandler($class)
	{
		static $libs = array('autoload', 'cookie', 'session', 'html', 'l10n', 'form', 'db', 'db-table', 'db-structure', 'paginator', 'data-grid');

		$ci_class = Tools::dash($class);
		if (in_array($ci_class, $libs))
			$this->loadCore($class, false);

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