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


ob_start();
$startTime = microtime(true);

/******************** LIBRARIES ********************/
require_once dirname(__FILE__) . '/libs/tools.php';
require_once dirname(__FILE__) . '/libs/http.php';
require_once dirname(__FILE__) . '/libs/object.php';
require_once dirname(__FILE__) . '/libs/cache.php';

/******************** CORE ********************/
require_once dirname(__FILE__) . '/application/exceptions.php';
require_once dirname(__FILE__) . '/application/inflector.php';
require_once dirname(__FILE__) . '/application/router.php';
require_once dirname(__FILE__) . '/application/config.php';




class Application extends Object
{


	/** @var bool Error mode */
	public static $error = false;

	/** @var Application */
	private static $self;


	/**
	 * Return instance of Application
	 * @return  Application
	 */
	public static function i()
	{
		return self::$self;
	}




	/** @var string */
	private $path;

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

		$this->path = trim(dirname($_SERVER['SCRIPT_FILENAME']) . $path, '/');
		spl_autoload_register(array($this, 'autoloadHandler'));
		set_exception_handler(array($this, 'exceptionHandler'));

		if ($config !== false)
			$this->loadConfig($this->path . $config);
	}


	/**
	 * Destructor
	 * @return  void
	 */
	public function __destruct()
	{
		if (Config::read('Core.debug') > 1)
			Debug::debugToolbar();
	}


	/**
	 * Parser file configuration and load the configuration
	 * @param   string  filename
	 * @return  void
	 */
	public function loadConfig($file)
	{
		if (!is_file($file))
			throw new Exception("Missing configuration file '$file'.");

		Config::multiWrite(Config::parseFile($file));

		$this->setDebugMode();
		Cache::$store = Config::read('Cache.store', $this->path . '/temp/cache/');
		Cache::$enabled = true;
	}


	/**
	 * Load framework core file
	 * @param   string  filename
	 * @return  void
	 */
	public function loadCore($file)
	{
		$file = dirname(__FILE__) . "/$file.php";
		if (!file_exists($file))
			throw new Exception("Missing core file '$file'.");

		require_once $file;
	}


	/**
	 * Load framework file
	 * Try load in appliacation directory, then in framework core direcotry
	 * @param   string  filename
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
	 * Load framework class
	 * @param   string  class type
	 * @param   string  class name
	 * @return  void
	 */
	public function loadClass($type, $class)
	{
		static $types = array('controller', 'sql', 'helper');
		if (!in_array($type, $types))
			throw new Exception("Unsupported class-type '$type'.");


		$file = call_user_func_array(array('Inflector', "{$type}File"), array($class));
		$this->loadFile($file, true);

		if (!class_exists($class, false))
			throw new ApplicationException('missing-' . $type, $class);
	}




	/**
	 * Activate autoload for "/app/extends" and others
	 * @param   array      directories for autoload
	 * @return  Autoload
	 */
	public function autoload($dirs = array())
	{
		$autoload = new Autoload();

		$autoload->exts = Config::read('Autoload.exts', $autoload->exts);
		$autoload->cache = Config::read('Autoload.cache', "{$this->path}/temp/cache/{$autoload->cache}");
		$autoload->autoRebuild = Config::read('Core.debug') > 0;
		$autoload->addDir("{$this->path}/extends/");

		foreach ((array) $dirs as $dir)
			$autoload->addDir($dir);

		$autoload->load();
		return $autoload;
	}


	/**
	 * Run the application
	 * @return  void
	 */
	public function run()
	{
		try {
			$this->loadClass('controller', 'AppController');
		} catch (ApplicationException $e) {
			eval('class AppController extends Controller {}');
		}

		if (empty(Router::$controller))
			throw new ApplicationException('routing');

		$class = Inflector::controllerClass(Router::$controller, Router::$namespace);
		$this->loadClass('controller', $class);

		$this->controller = new $class;
		$this->controller->render();
	}



	/**
	 * Zachyti vyjimky render chybovou stranku
	 * @param   Exception   nezachycena vyjimka
	 * @return  void
	 */
	public function exceptionHandler(Exception $exception)
	{
		try {

			// render application layout
			if ($exception instanceof ApplicationException || Config::read('Core.debug') == 0) {

				Router::$service = null;

				$this->controller = new AppController();

				if ($exception instanceof ApplicationException) {
					$this->controller->error($exception->error, true);
					$this->controller->view->message = $exception->getMessage();
				} else {
					$this->controller->error('500');
					error_log($exception->getMessage());
				}

				$this->controller->view->loadHelpers();
				$this->controller->init();
				echo $this->controller->view->render();

			// render debug template
			} else {
				Debug::exceptionHandler($exception);
			}

		// render temporary message
		} catch (Exception $e) {
			Debug::dump($e);
		}
	}


	/**
	 * Application autoload handler
	 * @param   string  class name
	 * @return  void
	 */
	public function autoloadHandler($class)
	{
		static $core = array(
			'autoload' => 'libs/autoload',
			'cookie' => 'libs/cookie',
			'session' => 'libs/session',
			'debug' => 'libs/debug',
			'html' => 'libs/html',
			'db' => 'libs/db',
			'appform' => 'application/app-form',
			'l10n' => 'application/l10n'
		);

		if (isset($core[strtolower($class)]))
			$this->loadCore($core[strtolower($class)]);
		elseif (Tools::endWith(strtolower($class), 'sql'))
			$this->loadClass('sql', $class);
		elseif (Tools::endWith(strtolower($class), 'controller'))
			$this->loadClass('controller', $class);
		elseif (Tools::endWith(strtolower($class), 'helper'))
			$this->loadClass('helper', $class);
	}


	/**
	 * Set debug configuration
	 * @return  void
	 */
	private function setDebugMode()
	{
		if (Config::read('Core.debug') > 0) {
			ini_set('display_errors', true);
			ini_set('error_reporting', E_ALL);
		} else {
			ini_set('display_errors', false);
			ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE);
			ini_set('log_errors', true);
			ini_set('error_log', Config::read('Core.log', "{$this->path}/temp/errors.log"));
		}
	}


	public function getController()
	{
		return $this->controller;
	}

	public function getPath()
	{
		return $this->path;
	}

}