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
                throw new Exception('Knihovna jadra nebyla nalezena!');
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
     * Zobrazi chybovou chybovou zpravu.
     * Pokud je ladici rezim vypnut, zobrazi se chyba 404.
     * @param   string  jmeno view
     * @param   bool    nahradit v non-debug 404
     * @return  void
     */
    public function error($view = '404', $debug = false)
    {
        $this->error = true;

        if ($debug === true && Config::read('Core.debug', 0) === 0) {
            Http::error('404');
            $this->controller->view->view('404');
        } else {
            $this->controller->view->view($view);
        }
    }



    /**
     * Zapne zobrazeni chyb a odchyceni vyjimky pro vyvojare
     * @return  void
     */
    public function debugMode()
    {
        $this->loadCore('Debug');
        set_exception_handler(array('Debug', 'exceptionHandler'));
        ini_set('show_errors', true);
        ini_set('error_reporting', E_ALL);
    }



    /**
     * Zapne logovani chyb a odchyceni vyjimky pro koncove uzivatele
     * @return  void
     */
    public function nonDebugMode()
    {
        set_exception_handler(array($this, 'exceptionHandler'));
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
        error_log($exception->getMessage());
        $this->controller = new CustomController;
        $this->error('500');
        Http::error('500');
        $this->controller->view->render();
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
            require_once dirname(__FILE__) . '/CustomController.php';
            $this->controller = new CustomController;
            $this->error('routing', true);
        } elseif (!class_exists($class)) {
            require_once dirname(__FILE__) . '/CustomController.php';
            $this->controller = new CustomController;
            $this->error('controller', true);
            $this->controller->view->missingController = $class;
        } else {
            $this->controller = new $class;
        }
    }



}