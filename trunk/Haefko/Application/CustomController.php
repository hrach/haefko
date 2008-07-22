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



require_once dirname(__FILE__) . '/../Http.php';



/**
 * Zakladní trida CustomController, od ni musi byt odvozeny vsechny controllery
 */
abstract class CustomController
{



    public $app;
    public $view;
    public $model;
    public $ajax = false;
    public $load = array();
    public $helpers = array();
    public $services = array('rss' => 'RssView');



    /**
     * Konstruktor
     * @param   string  jmeno view tridy
     * @return  void
     */
    public function __construct($viewClass = 'LayoutView')
    {
        $this->app  = Application::getInstance();
        $this->ajax = Http::isAjax();

        if (!$this->app->error) {
            foreach ($this->load as $file)
                Application::loadCore($file);
        }

        if (isset($this->services[Router::$service]))
            $viewClass = $this->services[Router::$service];
        elseif ($this->ajax)
            $viewClass = 'View';

        Application::loadCore("Application/$viewClass");
        $this->view = new $viewClass($this);


        Application::loadCore('Application/Db');

        if (!Application::load('Model', 'model', array('model', ''), true)) {
            $created = true;
            eval ('class Model extends CustomModel {}');
        } else {
            $created = false;
        }

        $class = Inflector::modelClass(Router::$controller, Router::$namespace);
        Application::load($class, 'model', array(Router::$controller, Router::$namespace), true);

        if (class_exists($class) && !($this->app->error && $created))
            $this->model = new $class($this);
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

        if ($debug === true && Config::read('Core.debug') == 0) {
            Http::error(404);
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
        if ($exit)
            exit;
    }



    /**
     * Vytvori URL v ramci aplikace
     * @param   string  url
     * @param   bool    absolutni url
     * @return  string
     */
    public function url($link, $absolute = false)
    {
        $url = preg_replace('#\{url\}#', Router::getUrl(), $link);
        $url = preg_replace('#\{\:(\w+)\}#e', 'isset(Router::$args["\\1"]) ? Router::$args["\\1"] : "\\0"', $url);
        $url = preg_replace('#\{args\}#e', 'implode("/", Router::$args)', $url);
        $url = preg_replace_callback('#\{args!(.+)\}#', array($this, 'urlArgs'), $url);
        $url = strSanitizeUrl($url);

        if ($absolute)
            return Http::$serverUri . Http::$baseUri . $url;
        else
            return Http::$baseUri . $url;
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
            if ($named === true)
                return strLeftTrim(Router::$args[$name], "$name:");
            elseif ($named === false)
                return Router::$args[$name];
            else
                return strLeftTrim(Router::$args[$name], "$named:");
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

        if ($this->ajax && method_exists(get_class($this), $method . 'Ajax'))
            $method .= 'Ajax';

        $exists = method_exists(get_class($this), $method);

        if ($exists)
            $this->view->view(Router::$action);
        elseif (!$this->app->error)
            throw new ApplicationException('method', $method);

        $this->view->loadHelpers();

        call_user_func(array($this, 'init'));
        if ($exists) call_user_func_array(array($this, $method), Router::$args);
        call_user_func(array($this, 'renderInit'));

        echo $this->view->render();
    }



    /**
     * Vrati cas url s pozadovanymi argumenty
     * @param   array   matches
     * @return  string
     */
    private function urlArgs($matches)
    {
        $args = array();
        $matches = array_diff(array_keys(Router::$args), explode(',', $matches[1]));

        foreach ($matches as $match)
            $args[] = Router::$args[$match];

        return implode('/', $args);
    }




}