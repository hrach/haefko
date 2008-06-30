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



/**
 * Zakladní trida CustomController, od ni musi byt odvozeny vsechny controllery
 */
abstract class CustomController
{


    public $load = array();
    public $services = array(
        'rss' => 'RssView'
    );

    public $app;
    public $view;
    public $model;



    /**
     * Konstruktor
     * @param   string  jmeno view tridy
     * @return  void
     */
    public function __construct($viewClass = 'LayoutView')
    {
        $this->app  = Application::getInstance();

        foreach ($this->load as $file) {
            Application::loadCore($file);
        }

        if (isset($this->services[Router::$service])) {
            $viewClass = $this->services[Router::$service];
        }

        Application::loadCore("Application/$viewClass");
        $this->view = new $viewClass($this);


        $class = Inflector::modelClass(Router::$controller, Router::$namespace);
        Application::loadCore('Application/Db');
        Application::load($class, 'model', array(Router::$controller, Router::$namespace));

        if (class_exists($class)) {
            $this->model = new $class($this);
        }
    }



    /**
     * Metoda init je zavolana vzdy po vytvoreni controlleru, jeste pred zavolanim action
     */
    public function init()
    {}



    /**
     * Metoda renderInit je zavolana vzdy pred vyrenderovanim sablony, po zavolani action
     */
    public function renderInit()
    {}



    /**
     * Zobrazi chybovou chybovou zpravu.
     * Pokud je ladici rezim vypnut, zobrazi se chyba 404.
     * @param   string  jmeno view
     * @param   bool    nahradit v non-debug 404
     * @return  void
     */
    public function error($view = '404', $debug = false)
    {
        $this->app->error = true;

        if ($debug === true && Config::read('Core.debug', 0) === 0) {
            Http::error('404');
            $this->view->view('404');
        } else {
            $this->view->view($view);
        }
    }



    /**
     * Presmeruje na novou url v ramci aplikace
     * @param   string  url - relativni
     * @param   bool    zavolat po presmerovani exit
     * @return  void
     */
    public function redirect($url, $exit = true)
    {
        Http::redirect($this->url($url, true), 303);
        if ($exit) exit;
    }



    /**
     * Vytvori URL v ramci aplikace
     * @param   string  url
     * @param   bool    absolutni url
     * @return  string
     */
    public function url($link, $absolute = false)
    {
        $newUrl = array();
        $url = Strings::urlToArray($link);

        foreach ($url as $val) {
            if (preg_match('#\{(args|!args)(?:\:(.+)(?:,(.+))*)?\}#U', $val, $matchs)) {
                $tag = $matchs[1];
                unset($matchs[0], $matchs[1]);
                if (count($matchs) == 0) {
                    $matchs = array_keys(Router::$args);
                } elseif ($tag == '!args') {
                    $matchs = array_diff(array_keys(Router::$args), $matchs);
                }
                foreach ($matchs as $match) {
                    if (isset(Router::$args[$match])) {
                        $newUrl[] = Router::$args[$match];
                    }
                }
            } elseif ($val == '{url}' && Router::getUrl() != '') {
                $newUrl[] = Router::getUrl();
            } else {
                $newUrl[] = $val;
            }
        }

        if ($absolute) {
            return Http::$serverUri . Http::$baseUri . implode('/', $newUrl);
        } else {
            return Http::$baseUri . implode('/', $newUrl);
        }
    }



    /**
     * Vrati hodnotu jmenneho argumentu
     * @param   string  jmeno argumentu
     * @param   mixed   defaultni hodnota
     * @param   mixed   bool/string - jedna se o jemnny argument/odstranic dany prefix
     * @return  mixed
     */
    public function getArg($name, $default = false, $named = true)
    {
        if (isset(Router::$args[$name])) {
            if ($named === true) {
                return Strings::ltrim(Router::$args[$name], "$name:");
            } elseif ($named === false) {
                return Router::$args[$name];
            } else {
                return Strings::ltrim(Router::$args[$name], "$named:");
            }
        } else {
            return $default;
        }
    }



    /**
     * Spusti volani action a rendering
     * @return  void
     */
    public function render()
    {
        $method = Inflector::actionName(Router::$action);
        $exists = method_exists(get_class($this), $method);


        if ($exists) {
            $this->view->view(Router::$action);
        } elseif(!$this->app->error) {
            throw new ApplicationException('method', $method);
        }


        call_user_func(array($this, 'init'));
        call_user_func(array($this->view, 'init'));

        if ($exists) {
            call_user_func_array(array($this, $method), Router::$args);
        }

        call_user_func(array($this, 'renderInit'));

        echo $this->view->render();
    }



}