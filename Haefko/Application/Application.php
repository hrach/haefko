<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.6
 * @package     Haefko
 */



require_once dirname(__FILE__) . '/../Http.php';
require_once dirname(__FILE__) . '/../Strings.php';
require_once dirname(__FILE__) . '/../Config.php';



/**
 * Trida ridici webovou aplikaci
 */
class Application
{



    public static $frameworkVersion = '0.6';

    private static $app = false;



    public static function getInstance()
    {
        if (self::$app === false) {
            self::$app = new Application();
            self::$app->setPath('/app/');

            set_exception_handler(array(self::$app, 'exceptionHandler'));
        }

        return self::$app;
    }



    public $controller;
    public $error = false;

    private $appPath;



    /**
     * Nacte knihovnu jadra frameworku
     * @param   string      jmeno knihovny
     * @return  Application
     */
    public function loadCore($class)
    {
        $classes = func_get_args();

        foreach ($classes as $class) {
            $classFile = dirname(__FILE__) . '/../' . $class . '.php';
            if (file_exists($classFile)) {
                require_once $classFile;
            } else {
                throw new Exception('Knihovna jadra frameworku nebyla nalezena: ' . $classFile);
            }
        }

        return $this;
    }



    /**
     * Nacte konfiguracni soubor
     * @param   string      jmeno souboru
     * @return  Application
     */
    public function loadConfig($file = 'config.yaml')
    {
        if (file_exists($this->getPath() . $file)) {
            Config::load($this->getPath() . $file);
        } else {
            throw new Exception('Konfiguracni soubor neexistuje: ' . $file);
        }

        if (Config::read('Core.debug', 0) > 0) {
            $this->debugMode();
        } else {
            $this->nonDebugMode();
        }

        return $this;
    }




    /**
     * Spustí celou aplikaci
     * @return  void
     */
    public function run()
    {
        $this->createController(Router::$controller, Router::$namespace);
        $this->controller->render();

        if (Config::read('Core.debug', 0) > 1) {
            Debug::debugRibbon();
        }
    }




    /**
     * Nastavi cestu k souborum aplikace
     * @param   string      cesta
     * @return  Application
     */
    public function setPath($path)
    {
        $this->appPath = $path;
        return $this;
    }



    /**
     * Vrati cestu k souborum aplikace
     * @return  string
     */
    public function getPath()
    {
        return dirname($_SERVER['SCRIPT_FILENAME']) . $this->appPath;
    }



    /**
     * Vrati cestu k souborum frameworku
     * @return  string
     */
    public function getCorePath()
    {
        return dirname(__FILE__) . '/../';
    }



    /**
     * Zapne zobrazeni chyb a odchyceni vyjimky pro vyvojare
     * @return  void
     */
    public function debugMode()
    {
        $this->loadCore('Debug');
        ini_set('show_errors', true);
        ini_set('error_reporting', E_ALL);
    }



    /**
     * Zapne logovani chyb a odchyceni vyjimky pro koncove uzivatele
     * @return  void
     */
    public function nonDebugMode()
    {
        ini_set('display_errors', false);
        ini_set('error_reporting', E_ERROR | E_PARSE);
        ini_set('log_errors', true);
        ini_set('error_log', Config::read('Core.debug-file', $this->getPath() . 'temp/errors.log.dat'));
    }



    /**
     * Zachyti vyjimky render chybovou stranku
     * @param   Exception   nezachycena vyjimka
     * @return  void
     */
    public function exceptionHandler(Exception $exception)
    {
        if ($exception instanceof HFException) {

            static $codeToViewName = array(
                1 => 'controller',
                2 => 'method',
                3 => 'routing',
                4 => 'view'
            );

            $this->loadCore('Application/CustomController');
            $this->controller = new CustomController;

            if ($this->controller->view instanceof RssView) {
                $this->controller->view = new View();
            }

            $this->controller->view->message = $exception->getMessage();
            if (isset($codeToViewName[$exception->getCode()])) {
                $this->controller->error($codeToViewName[$exception->getCode()]);
            } else {
                throw new Exception('Nepodporovany kod HFException: ' . $exception->getCode());
            }

            $this->controller->view->render();

        } elseif (Config::read('Code.debug', 0) === 0) {

            error_log($exception->getMessage());

            $this->loadCore('Application/CustomController');
            $this->controller = new CustomController;

            $this->controller->error('500');
            $this->controller->view->render();

        } else {

            $this->loadCore('Debug');
            Debug::exceptionHandler($exception);

        }
    }



    /**
     * Vytvori controller, pripadne zavola prislusne chybove metody
     * @param   string  jmeno controlleru
     * @param   string  namespace contrlleru
     * @return  void
     */
    private function createController($controller, $namespace)
    {
        $class = Strings::camelize($namespace) . Strings::camelize($controller) . 'Controller';

        if ($class == 'Controller') {
            $this->loadCore('Application/Exceptions');
            throw new HFException(null, 3);
        } elseif (!class_exists($class)) {
            $this->loadCore('Application/Exceptions');
            throw new HFException($class ,1);
        } else {
            $this->controller = new $class;
        }
    }



}