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

    /** @var Application Reference na instanci aplikace */
    public $app;

    /** @var View */
    public $view;

    /** @var Model */
    public $model;

    /** @var bool Je aplikace volána pøes Ajax? */
    public $ajax = false;

    /** @var array Pole s požadovanými helpery */
    public $helpers = array();

    /** @var array Pole s vazbami service => ViewClass */
    protected $services = array(
        'rss' => 'RssView'
    );



    /**
     * Konstruktor
     * @param   string  jmeno view tridy
     * @return  void
     */
    public function __construct($viewClass = 'LayoutView')
    {
        $this->app = Application::getInstance();
        $this->ajax = Http::isAjax();


        // Nacteni view

        if (isset($this->services[Router::$service]))
            $viewClass = $this->services[Router::$service];
        elseif ($this->ajax)
            $viewClass = 'View';

        $this->app->loadCore("Application/$viewClass");
        $this->view = new $viewClass($this);


        // Nacteni modelu

        $this->app->loadCore('Application/Db');

        if (!$this->app->loadFrameworkFile(Inflector::modelFile('Model'), false)) {
            $eval = true;
            eval ('class Model extends CustomModel {}');
        }

        $class = Inflector::modelClass(Router::$controller, Router::$namespace);
        $this->app->loadFrameworkFile(Inflector::modelFile($class), false);

        if (!class_exists($class, false)) {
            $class = Inflector::modelClass(Router::$controller, '');
            $this->app->loadFrameworkFile(Inflector::modelFile($class), false);
        }

        if (!class_exists($class, false) && !($this->app->error) && !isset($eval))
            $class = 'Model';

        if (class_exists($class, false))
            $this->model = new $class($this);
    }



    /**
     * Metoda init je zavolana vzdy pred zavolanim action
     */
    public function init()
    {
        if (isset($this->model))
            call_user_func(array($this->model, 'init'));
    }



    /**
     * Metoda renderInit je zavolana vzdy pred vyrenderovanim sablony, po zavolani action
     */
    public function prepareView()
    {
        if (isset($this->model))
            call_user_func(array($this->model, 'prepareView'));
    }



    /**
     * Metoda prepareLayout je zavolana vzdy pred vyrenderovanim layout sablony
     */
    public function prepareLayout()
    {
        if (isset($this->model))
            call_user_func(array($this->model, 'prepareLayout'));
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
    public function url($url, $absolute = false)
    {
        $url = preg_replace('#\{url\}#', Router::getUrl(), $url);
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
    public function getArg($variable, $default = null, $name = null)
    {
        if (isset(Router::$args[$variable])) {
            if (!empty($name))
                return strLeftTrim(Router::$args[$variable], "$variable:");
            else
                return strLeftTrim(Router::$args[$variable], "$name:");
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

        if ($this->ajax && method_exists(get_class($this), $method . 'AjaxAction'))
            $method .= 'AjaxAction';
        else
            $method .= 'Action';

        $exists = method_exists(get_class($this), $method);

        if ($exists)
            $this->view->view(Router::$action);
        elseif (!$this->app->error)
            throw new ApplicationException('missing-method', $method);

        $this->view->loadHelpers();

        call_user_func(array($this, 'init'));
        if ($exists) call_user_func_array(array($this, $method), Router::$args);

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