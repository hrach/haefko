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



/**
 * Zakladni konstra pro View sablony
 */
abstract class CustomView
{



    public $controller;
    public $base;

    protected $ext = '.phtml';
    protected $vars = array();

    protected $viewPath;
    protected $viewName;
    protected $themeName;
    protected $absoluteView = false;



    /**
     * Konstruktor
     * @return  void
     */
    public function __construct()
    {
        $this->controller = & Application::getInstance()->controller;
        $this->base = Http::getInternalUrl();

        $this->vars['escape'] = 'htmlSpecialChars';
        $this->vars['title'] = 'Haefko - php5 framework';
    }



    /**
     * Nastaví view sablonu
     * @param   string jmeno sablony
     * @param   bool   nedoplnit adresarovou strukturu?
     * @return  void
     */
    public function view($viewName, $absoluteView = false)
    {
        $this->viewName = $viewName;
        $this->absoluteView = $absoluteView;
    }



    /**
     * Vrati jmeno view sablony, bez pripony
     * @return  string
     */
    public function getView()
    {
        return $this->viewName;
    }



    /**
     * Vrati cestu k view sablone
     * @return  string
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }



    /**
     * Nastavi view tema
     * Temata vypnete pomoci false
     * @param   string  jmeno tematu
     * @return  void
     */
    public function theme($themeName)
    {
        $this->themeName = $themeName;
    }



    /**
     * Vrati jmeno tematu
     * @return  mixed
     */
    public function getTheme()
    {
        return $this->themeName;
    }



    /**
     * Ulozi do seznamu promennych pro sablonu
     * @param   string  jmeno promenne
     * @param   mixed   hodnota promenne
     * @return  void
     */
    public function __set($name, $value)
    {
        $this->vars[$name] = $value;
    }



    /**
     * Vrati hodnotu z promennych pro sablonu
     * @param   string  jmeno promenne
     * @return  mixed
     */
    public function &__get($name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        } else {
            throw new Exception('Neexistujici promenna: ' . $name);
        }
    }



    /**
     * Vytvori cestu k view sablone
     * V pripade chyby vola prislusnou chybovou zpravu
     * @return  void
     */
    protected function pathFactory()
    {
        $app = Application::getInstance();

        if (empty($this->themeName)) {
            $theme = '';
        } else {
            $theme = $this->themeName . '/';
        }

        $namespace = Router::$namespace;
        $controller = Router::$controller;

        if ($app->error) {
            $view = 'views/_errors/';
        } else {
            if (!$this->absoluteView) {
                $view = "views/$theme" . Strings::underscore($namespace . $controller) . '/';
                if (!empty(Router::$service)) {
                    $view .= 'service/';
                }
            } else {
                $view = 'views/';
            }
        }

        $view .= Strings::underscore($this->viewName) . $this->ext;

        if (file_exists($app->getPath() . $view)) {
            return $app->getPath() . $view;
        } elseif ($app->error && file_exists($app->getCorePath() . $view)) {
            return $app->getCorePath() . $view;
        } else {
            if ($app->error && $this->viewName == 'view') {
                throw new Exception('View: Soubory Haefka poskozeny - chybi: ' . $view);
            } else {
                $app->loadCore('Application/Exceptions');
                throw new HFException($view, 4);
            }
        }
    }



}