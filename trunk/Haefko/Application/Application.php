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
require_once dirname(__FILE__) . '/../Config.php';
require_once dirname(__FILE__) . '/Exceptions.php';
require_once dirname(__FILE__) . '/Inflector.php';
require_once dirname(__FILE__) . '/Router.php';
require_once dirname(__FILE__) . '/CustomController.php';
require_once dirname(__FILE__) . '/View.php';



/**
 * Trida ridici webovou aplikaci
 */
class Application
{



    public static $version = '0.7.0.25';
    private static $app;



    /**
     * Vytvori instanci objektu aplikace
     * @param   string  cesta k adresari aplikace
     * @param   mixed   jmeno konfiguracniho souboru aplikace
     * @return  Application
     */
    public static function create($path = '/app/', $config = 'config.yml')
    {
        if (self::$app instanceof Application)
            die('Haefko: aplikace je jiz vytvorena!');

        self::$app = new Application();
        self::$app->path = $path;

        if ($config !== false)
            self::$app->loadConfig($config);

        return self::$app;
    }



    /**
     * Vrati instanci obejktu aplikace
     * @return  Application
     */
    public static function getInstance()
    {
        return self::$app;
    }



    /**
     * Nacte tridu pomoci autoloadu nebo pomoci jmennych konvenci
     * @param   string  jmeno tridy
     * @param   string  typ tridy (controller, model, ...)
     * @param   array   argumenty pro Inflector
     * @param   bool    vratit bool hodnotu, false == vyhodi vyjimku
     * @return  bool
     */
    public static function load($class, $type, array $args, $return = false)
    {
        static $types = array('controller', 'model', 'view', 'helper');

        if (!in_array($type, $types))
            throw new Exception("Nepodporovany typ Application::load $type!");

        $file = self::getInstance()->getPath() . call_user_func_array(array('Inflector', $type . 'File'), $args);

        if (!class_exists($class) && !file_exists($file)) {
            if (!$return)
                throw new ApplicationException($type, $class);
            else
                return false;
        } elseif (file_exists($file)) {
            require_once $file;
        }

        return true;
    }



    /**
     * Nahraje pozadovany soubor aplikace
     * @param   string  cesta k souboru
     * @return  void
     */
    public static function loadApp($file)
    {
        $app = self::getInstance();
        $file = $app->getPath() . $file;

        if (file_exists($file))
            require_once $file;
        else
            die("Haefko: nenalezen soubor $file!");
    }



    /**
     * Nacte knihovnu jadra frameworku
     * @param   string      jmeno knihovny
     * @return  void
     */
    public static function loadCore($file)
    {
        $file = dirname(__FILE__) . "/../$file.php";

        if (file_exists($file))
            require_once $file;
        else
            die("Haefko: nenalezen soubor $file!");
    }



    public $path;
    public $controller;
    public $error = false;
    public $autoload = false;



    /**
     * Konstruktor aplikace
     * @return  void
     */
    public function __construct()
    {
        set_exception_handler(array($this, 'exceptionHandler'));
    }



    /**
     * Nacte konfiguracni soubor
     * @param   string      jmeno souboru
     * @return  Application
     */
    public function loadConfig($file)
    {
        $file = $this->getPath() . $file;

        if (file_exists($file))
            Config::load($file);
        else
            die("Haefko: nenalezen konfiguracni soubor $file!");

        if (Config::read('Core.debug', 0) > 0)
            $this->debugMode();
        else
            $this->nonDebugMode();

        return $this;
    }




    /**
     * Spustí celou aplikaci
     * @return  void
     */
    public function run()
    {
        $class = Inflector::controllerClass(Router::$controller, Router::$namespace);

        if (!Application::load('Controller', 'controller', array('controller', ''), true))
            eval('class Controller extends CustomController {}');

        if ($class == 'Controller')
            throw new ApplicationException('routing');
        else
            Application::load($class, 'controller', array(Router::$controller, Router::$namespace));

        $this->controller = new $class;
        $this->controller->render();

        if (Config::read('Core.debug', 0) > 1)
            Debug::debugRibbon();
    }



    /**
     * Vrati cestu k souborum aplikace
     * @return  string
     */
    public function getPath()
    {
        return dirname($_SERVER['SCRIPT_FILENAME']) . $this->path;
    }



    /**
     * Vrati cestu k souborum frameworku
     * @return  string
     */
    public function getCorePath()
    {
        return dirname(__FILE__) . '/';
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
        ini_set('error_log', Config::read('Core.error.log', $this->getPath() . 'temp/errors.log.dat'));
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
            self::loadCore('Debug');
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
            Debug::debugRibbon();
    }



}