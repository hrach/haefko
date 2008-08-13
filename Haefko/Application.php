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
require_once dirname(__FILE__) . '/Application/Exceptions.php';
require_once dirname(__FILE__) . '/Application/Inflector.php';
require_once dirname(__FILE__) . '/Application/Router.php';
require_once dirname(__FILE__) . '/Application/CustomController.php';
require_once dirname(__FILE__) . '/Application/View.php';



/**
 * Trida Application spousti a ridi celou webovou aplikace
 */
final class Application
{

    /** @var string Verze frameworku */
    public static $version = '0.7.0.4';

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
    final public function __construct($path = '/app/', $config = 'config.yml')
    {
        self::$app = & $this;

        $this->path = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . trim($path, '/') . '/';
        $this->corePath = dirname(__FILE__) . '/';

        if ($config !== false)
            $this->loadConfig($config);

        spl_autoload_register(array($this, 'autoloadHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));
    }



    /**
     * Zapne autoload pro složky extends
     * @param   array   adresare ke skenovani
     * @return  Autoload
     */
    public function autoload(array $dirs = array())
    {
        $autoload = new Autoload();
        $autoload->addDir($this->path . 'extends/');

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
            throw new Exception("Application: nepodporovany typ '$type' metody loadClass().");

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
        $file1 = $this->path . $file;
        $file2 = $this->corePath . 'Application/' . $file;

        if (file_exists($file1))
            require_once $file1;
        elseif (file_exists($file2))
            require_once $file2;
        elseif ($throw && !$this->error)
            throw new ApplicationException('missing-file', $file);
        elseif ($throw)
            die("Application: soubor '$file' nenalezen.");
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
        $file = "{$this->corePath}$file.php";

        if (file_exists($file))
            require_once $file;
        else
            die("Application: soubor '$file' nenalezen.");
    }



    /**
     * Nacte konfiguracni soubor
     * @param   string  soubor
     * @return  void
     */
    public function loadConfig($file)
    {
        $file = $this->path . $file;

        if (file_exists($file))
            Config::load($file);
        else
            die("Application: konfiguracni soubor '$file' nebyl nalezen.");

        if (Config::read('Core.debug', 0) > 0)
            $this->debugMode();
        else
            $this->nonDebugMode();
    }



    /**
     * Spustí aplikaci
     * @return  void
     */
    public function run()
    {
        $class = Inflector::controllerClass(Router::$controller, Router::$namespace);

        if (!Application::loadFrameworkFile(Inflector::controllerFile('Controller'), false))
            eval('class Controller extends CustomController {}');


        if ($class == 'Controller')
            throw new ApplicationException('routing');
        else
            Application::loadClass('controller', $class, true);

        $this->controller = new $class;
        $this->controller->render();

        if (Config::read('Core.debug', 0) > 1 && !$this->controller->ajax)
            Debug::debugToolbar();
    }



    /**
     * Zapne zobrazeni chyb a odchyceni vyjimky pro vyvojare
     * @return  void
     */
    public function debugMode()
    {
        $this->loadCore('Debug');
        ini_set('display_errors', true);
        ini_set('error_reporting', E_ALL);
    }



    /**
     * Zapne logovani chyb a odchyceni vyjimky pro koncove uzivatele
     * @return  void
     */
    public function nonDebugMode()
    {
        ini_set('display_errors', false);
        ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE);
        ini_set('log_errors', true);
        ini_set('error_log', Config::read('Core.log', "{$this->path}temp/errors.log.dat"));
    }



    /**
     * Zachyti vyjimky render chybovou stranku
     * @param   Exception   nezachycena vyjimka
     * @return  void
     */
    public function exceptionHandler(Exception $exception)
    {
        $this->error = true;
        Router::$service = null;

        if (!($exception instanceof ApplicationException || Config::read('Core.debug') == 0)) {
            Debug::exceptionHandler($exception);
        } else {
            $this->controller = new Controller();

            if ($exception instanceof ApplicationException) {
                $this->controller->error($exception->error, true);
                $this->controller->view->message = $exception->getMessage();
            } else {
                error_log($exception->getMessage());
                $this->controller->error('500');
            }

            $this->controller->view->loadHelpers();
            $this->controller->init();
            echo $this->controller->view->render();

        }

        if (Config::read('Core.debug', 0) > 1)
            Debug::debugToolbar();
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
            'Db' => 'Application/Db',
            'L10n' => 'Application/L10n',
            'LayoutView' => 'Application/LayoutView',
            'RssView' => 'Application/RssView'
        );

        if (isset($core[$class]))
            require_once $this->corePath . $core[$class] . '.php';
        elseif (strpos($class, 'Controller'))
            $this->loadClass('controller', $class);
        elseif (strpos($class, 'Model'))
            $this->loadClass('model', $class);
        elseif (strpos($class, 'Helper'))
            $this->loadClass('helper', $class);
    }



}