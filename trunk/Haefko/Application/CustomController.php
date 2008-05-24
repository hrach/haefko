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
class CustomController
{



    public $services = array('rss' => 'RssView', 'atom' => 'RssView');

    public $app;
    public $view;
    public $model;



    /**
     * Konstruktor
     * @param   string  jmeno view tridy
     * @return  void
     */
    public function __construct($viewClass = 'View')
    {
        $this->app  = Application::getInstance();

        if (!$this->app->error && !empty(Router::$service) && isset($this->services[Router::$service])) {
            $this->app->loadCore('Application/' . $this->services[Router::$service]);
            $this->view = new $this->services[Router::$service]($this);
        } else {
            $this->app->loadCore('Application/View');
            $this->view = new $viewClass($this);
        }

        $modelClass = get_class($this);
        Strings::rtrim($modelClass, 'Controller');
        $modelClass .= 'Model';
        if ($modelClass !== 'CustomModel' && class_exists($modelClass)) {
            $this->app->loadCore('Db/Db');
            $this->model = new $modelClass($this);
        }
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
            return Http::getServerUrl() . Http::getInternalUrl() . implode('/', $newUrl);
        } else {
            return Http::getInternalUrl() . implode('/', $newUrl);
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
                $var = Router::$args[$name];
                Strings::ltrim($var, "$name:");
                return $var;
            } elseif ($named === false) {
                return Router::$args[$name];
            } else {
                $var = Router::$args[$name];
                Strings::ltrim($var, "$named:");
                return $var;
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
        static $run = false;

        if ($run === false) {
            $run = true;

            $methodName = Router::$action . 'Action';
            $methodExists = method_exists(get_class($this), $methodName);

            if (!$methodExists) {
                $app = Application::getInstance();
                if (!$app->error) {
                    throw new HFException($methodName, 2);
                }
            } else {
                $this->view->view(Router::$action);
            }

            if (method_exists($this, 'init')) {
                call_user_func(array($this, 'init'));
            }

            if ($methodExists) {
                call_user_func_array(array($this, $methodName), Router::$args);
            }

            if (method_exists($this, 'renderInit')) {
                call_user_func(array($this, 'renderInit'));
            }

            $this->view->render();
        }
    }



}