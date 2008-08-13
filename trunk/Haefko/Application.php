<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.7
 * @package     Haefko
 */



ob_start();
require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/Http.php';
require_once dirname(__FILE__) . '/Event.php';
require_once dirname(__FILE__) . '/Application/Exceptions.php';
require_once dirname(__FILE__) . '/Application/Inflector.php';
require_once dirname(__FILE__) . '/Application/Router.php';
require_once dirname(__FILE__) . '/Application/CustomController.php';
require_once dirname(__FILE__) . '/Application/CustomModel.php';
require_once dirname(__FILE__) . '/Application/View.php';



/**
 * Trida Application spousti a ridi celou webovou aplikace
 */
class Application
{

	/** @var string Verze frameworku */
	public static $version = '0.8';

	/** @var Application */
	private static $app;

	/** @var string Cesta k aplikaci */
	public $path;

	/** @var string Cesta k frameworku */
	public $corePath;

	/** @var CustomController */
	public $controller;

	/** @var bool Je aplikace v chybovem stavu? */
	public $error = false;



	/**
	 * Vrati instanci Application
	 * @return  Application
	 */
	public static function getInstance()
	{
		return self::$app;
	}



	/**
	 * Konstruktor aplikace
	 * @param   string      cesta k adresari aplikace
	 * @param   string|bool jmeno konfiguracniho souboru aplikace
	 * @return  void
	 */
	public function __construct($path = '/app', $config = '/config.yml')
	{
		self::$app = & $this;

		$this->path = trim(dirname($_SERVER['SCRIPT_FILENAME']) . $path, '/');
		$this->corePath = dirname(__FILE__);

		spl_autoload_register(array($this, 'autoloadHandler'));
		set_exception_handler(array($this, 'exceptionHandler'));

		// load configuration
		if ($config !== false)
			Config::load($this->path . $config);

		// set error mode
		if (Config::read('Core.debug') > 0) {
			$this->loadCore('Debug');
			ini_set('display_errors', true);
			ini_set('error_reporting', E_ALL);
		} else {
			ini_set('display_errors', false);
			ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE);
			ini_set('log_errors', true);
			ini_set('error_log', Config::read('Core.log', $this->path . '/temp/errors.log.dat'));
		}
	}



	/**
	 * Destructor
	 * @return  void
	 */
	public function __destruct()
	{
		if (Config::read('Core.debug', 0) > 1)
			Debug::debugToolbar();
	}



	/**
	 * Zapne autoload pro složky extends
	 * @param   array   adresare ke skenovani
	 * @return  Autoload
	 */
	public function autoload(array $dirs = array())
	{
		$autoload = new Autoload();
		$autoload->addDir($this->path . '/extends/');

		foreach ($dirs as $dir)
			$autoload->addDir($dir);

		$autoload->load();
		return $autoload;
	}



	/**
	 * Nacte soubor aplikace
	 * @param   string  typ tridy
	 * @param   string  jmeno tridy
	 * @return  bool
	 */
	public function loadClass($type, $class)
	{
		static $types = array('controller', 'model', 'helper');

		if (!in_array($type, $types))
			throw new Exception("Unsupported type '$type' of method Application::loadClass().");

		$file = call_user_func_array(array('Inflector', "{$type}File"), array($class));
		$this->loadFrameworkFile($file, true);

		if (!class_exists($class, false))
			throw new ApplicationException('missing-' . $type, $class);
	}



	/**
	 * Nacte soubor aplikace, v pripade nedostupnosti nacte jej z core
	 * @param   string  jmeno souboru
	 * @param   bool    vyhodit vyjimku?
	 * @return  bool
	 */
	public function loadFrameworkFile($file, $throw = false)
	{
		$file1 = $this->path . "/$file";
		$file2 = $this->corePath . "/Application/$file";

		if (file_exists($file1))
			require_once $file1;
		elseif (file_exists($file2))
			require_once $file2;
		elseif ($throw)
			throw new ApplicationException('missing-file', $file);
		else
			return false;

		return true;
	}



	/**
	 * Nacte soubor frameworku
	 * @param   string  jmeno souboru
	 * @return  void
	 */
	public function loadCore($file)
	{
		$file = $this->corePath . "/$file.php";

		if (file_exists($file))
			require_once $file;
		else
			throw new Exception("Missing core file '$file'.");
	}



	/**
	 * Run the application
	 * @return  void
	 */
	public function run()
	{
		if (!Application::loadFrameworkFile(Inflector::controllerFile('Controller'), false))
			eval('class Controller extends CustomController {}');

		$class = Inflector::controllerClass(Router::$controller, Router::$namespace);
		if ($class == 'Controller')
			throw new ApplicationException('routing');
		else
			Application::loadClass('controller', $class, true);

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

				$this->error = true;
				$this->controller = new Controller();

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
	 * Autoload handler
	 * @param   string  Trida
	 * @return  void
	 */
	public function autoloadHandler($class)
	{
		static $core = array(
			'Autoload' => 'Autoload',
			'Cookie' => 'Cookie',
			'Session' => 'Session',
			'Debug' => 'Debug',
			'Form' => 'Form',
			'Html' => 'Html',
			'Db' => 'Db',
			'L10n' => 'Application/L10n',
			'LayoutView' => 'Application/LayoutView',
			'RssFeedView' => 'Application/RssFeedView'
		);

		if (isset($core[$class]))
			$this->loadCore($core[$class]);
		elseif (strpos($class, 'Controller'))
			$this->loadClass('controller', $class);
		elseif (strpos($class, 'Model'))
			$this->loadClass('model', $class);
		elseif (strpos($class, 'Helper'))
			$this->loadClass('helper', $class);
	}



}