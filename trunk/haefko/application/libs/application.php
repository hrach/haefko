<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id: application.php 143 2009-07-18 16:24:50Z skrasek.jan $
 * @package     Haefko
 */


require_once dirname(__FILE__) . '/../../libs/tools.php';
require_once dirname(__FILE__) . '/../../libs/object.php';
require_once dirname(__FILE__) . '/../../libs/http.php';
require_once dirname(__FILE__) . '/../../libs/debug.php';
require_once dirname(__FILE__) . '/../../libs/cache.php';
require_once dirname(__FILE__) . '/../../libs/config.php';
require_once dirname(__FILE__) . '/router.php';


/**
 * @property-read Router $router
 * @property-read Controller $controller
 * @property-read Cache $cache
 * @property-read string $path
 * @property-read string $corePath
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
	 * Returns framework version and logo
	 * @param bool $image image version?
	 * @return string
	 */
	public static function getFrameworkInfo($image = true)
	{
		if ($image)
			return '<a href="http://haefko.skrasek.com"><img src="http://haefko.skrasek.com/media/powered.png" class="hf-powered" /></a>';
		else
			return '<a href="http://haefko.skrasek.com">Haefko 0.9</a>';
	}


	/** @var string - Error controller name */
	public $errorControler = 'AppController';

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
	 * Constructor
	 * @param string $path application path
	 * @param string|false $config configuration file|don't load default
	 * @return Application
	 */
	public function __construct($path = '/app', $config = '/config.yml')
	{
		if (!empty(self::$self))
			throw new Exception('You can not create more then 1 instance of Application class.');

		self::$self = & $this;
		header('X-Powered-By: Haefko/0.9');
		$this->path = rtrim(dirname($_SERVER['SCRIPT_FILENAME']) . $path, '/');
		$this->corePath = rtrim(dirname(dirname(dirname(__FILE__))), '/');

		if ($config !== false)
			Config::multiWrite(Config::parseFile($this->path . $config));

		if (Config::read('cache.storage.relative', true))
			$cachePath = $this->path . Config::read('cache.storage.path', '/temp/');
		else
			$cachePath = Config::read('cache.storage.path');

		$this->router = new Router();
		$this->cache = new Cache(true, $cachePath);
		$this->initConfig();
	}


	/**
	 * Inits application configuraction
	 */
	public function initConfig()
	{
		$this->cache->enabled = (bool) Config::read('cache.enabled', true);
		Debug::$logFile = Config::read('debug.log', $this->path . '/temp/errors.log');
		switch (Config::read('core.debug', 0)) {
			case 0: error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING); break;
			case 1: error_reporting(E_ALL ^ E_NOTICE); break;
			case 2: error_reporting(E_ALL); break;
			default: error_reporting(E_ALL); break;
		}
	}


	/**
	 * Loads applications file - from appliacation path or framework core path
	 * @param string $file filename
	 * @throws ApplicationException
	 */
	public function loadFile($file)
	{
		$file1 = $this->path . "/$file";
		$file2 = $this->corePath . "/application/$file";
		if (file_exists($file1))
			return require_once $file1;
		elseif (file_exists($file2))
			return require_once $file2;

		throw new ApplicationException('missing-file', $file);
	}


	/**
	 * Loads application class
	 * @param string $class class name
	 * @throws Exception|ApplicationException
	 */
	public function loadControllerClass($class)
	{
		$file = str_replace(array('_-', '_'), array('/', '/'), Tools::dash($class));
		$this->loadFile("controllers/$file.php");
		if (!class_exists($class, false))
			throw new ApplicationException('missing-controller', $class);
	}


	/**
	 * Activates autoload for "/app/extends" and others
	 * @param array $dirs directories for autoload
	 * @return AutoLoader
	 */
	public function autoload($dirs = array())
	{
		$autoload = new AutoLoader($this->cache);
		$autoload->exts = Config::read('Autoload.exts', $autoload->exts);
		$autoload->autoRebuild = Config::read('Core.debug') > 1;

		$dirs = (array) $dirs;
		array_unshift($dirs, "{$this->path}/extends/");
		foreach ($dirs as $dir)
			$autoload->addDir($dir);

		return $autoload->register();
	}


	/**
	 * Runs the application
	 * @throws ApplicationException
	 */
	public function run()
	{
		$this->loadAppControllerClass();
		$routing = $this->router->getRouting();
		if ($this->router->routed === false || empty($routing['controller']))
			throw new ApplicationException('routing');


		$module = implode('_', $routing['module']);
		$class = $routing['controller'] . 'Controller';
		if (!empty($module))
			$class = $module . '_' . $class;

		$this->loadControllerClass($class);
		$this->controller = new $class();
		echo $this->controller->render();
	}


	/**
	 * Proccess application exceptions and renders error page/message
	 * @param Exception
	 */
	public function processException(Exception $exception)
	{
		if (isset($this->contorller) && $this->controller->routing->ajax) {
			Http::$response->error(500);
			if (Config::read('core.debug') == 0)
				echo json_encode(array('response' => 'Internal server error.'));
			else
				echo json_encode(array('response' => $exception->getMessage()));

			exit(1);
		}

		# show details when exception is no ApplicationException and debug level is not 0
		if (!($exception instanceof ApplicationException) && Config::read('core.debug') > 0) {
			Debug::showException($exception);
			exit(1);
		}

		self::$error = true;
		$class = $this->errorController;
		$this->loadAppControllerClass();
		$this->controller = new $class();
		$this->controller->init();
		$this->controller->loadLayoutTemplate();

		if ($exception instanceof ApplicationException) {
			if (Config::read('core.debug') > 0) {
				Http::$response->error(404);
				$this->controller->setErrorTemplate($exception->errorFile);
				$this->controller->template->variable = $exception->variable;
			} else {
				Debug::log($exception->getMessage());
				Http::$response->error(500);
				$this->controller->setErrorTemplate('500');
			}
		} else {
			$this->controller->setErrorTemplate('404');
		}

		echo $this->controller->template->render();
	}


	/**
	 * Returns application controller object
	 * @return Controller
	 */
	public function getController()
	{
		return $this->controller;
	}


	/**
	 * Returns application path
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}


	/**
	 * Returns framework core path
	 * @return string
	 */
	public function getCorePath()
	{
		return $this->corePath;
	}


	/**
	 * Returns application Router object
	 * @return Router
	 */
	public function getRouter()
	{
		return $this->router;
	}


	/**
	 * Returns application Cache object
	 * @return Cache
	 */
	public function getCache()
	{
		return $this->cache;
	}


	/**
	 * Loads or creates AppController
	 */
	private function loadAppControllerClass()
	{
		if (class_exists('AppController', false))
			return;

		try {
			$this->loadControllerClass('AppController');
		} catch (ApplicationException $e) {
			eval('class AppController extends Controller {}');
		}
	}


}


class ApplicationException extends Exception
{

	/** @var string - Error template name */
	public $errorFile;

	/** @var mixed */
	public $variable;


	/**
	 * Constructor
	 * @param string $variable error type
	 * @param string $errorType template variable
	 * @return ApplicationException
	 */
	public function __construct($errorType, $variable = null)
	{
		static $errors = array('routing', 'missing-controller', 'missing-method', 'missing-template', 'missing-helper', 'missing-file');
		if (!in_array($errorType, $errors))
			throw new Exception("Unsupported ApplicationException type '$error'.");

		$this->errorFile = $errorType;
		$this->variable = $variable;
		parent::__construct(ucfirst(str_replace('-', ' ', $errorType)) . " '$variable'.");
	}


}


class ApplicationError extends Exception
{

	/** @var string - Error template name */
	public $errorFile;


	/**
	 * Constructor
	 * @param string $template template name
	 * @param bool $debug is exception debuggable?
	 * @param int $errorCode http error code
	 */
	public function __construct($template, $debug = false, $errorCode = 404)
	{
		Application::$error = true;

		if ($debug === true && Config::read('Core.debug') == 0)
			$template = '404';

		if ($errorCode !== null)
			Http::$response->error($errorCode);

		$this->errorFile = $template;
		parent::__construct("Application error: $template.");
	}


}