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



require_once dirname(__FILE__) . '/IView.php';
require_once dirname(__FILE__) . '/../Html.php';



/**
 * Zakladni konstra pro View sablony
 */
class View implements IView
{



    public $controller;
    public $base;

    protected $ext = 'phtml';
    protected $vars = array();
    protected $protected = array();

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
        $this->base = Http::$baseUri;

        $this->set('escape', 'htmlSpecialChars', false);
        $this->set('title', 'Your title', false);
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
     * Nacte helper
     * @param   string  jmeno helperu
     * @param   string  jmeno promenne, ve ktere bude pristupny ve view
     */
    public function helper($name, $var = null)
    {
        $class = Inflector::helperClass($name);

        if ($this->controller->app->autoload && !class_exists($class)) {
            die("Haefko: nenalezen helper $class!");
        } else {
            $file = Inflector::helperFile($name);

            $appFile = $this->controller->app->getPath() . $file;
            $coreFile = $this->controller->app->getCorePath() . $file;

            if (file_exists($appFile)) {
                require_once $appFile;
            } elseif (file_exists($coreFile)) {
                require_once $coreFile;
            } else {
                die("Haefko: nenalezen helper $class!");
            }
        }

        if (is_null($var)) {
            $var = strtolower($name);
        }

        if (!isset($this->$var)) {
            $this->set($var, new $class);
        }

        return $this->$var;
    }



    /**
     * Nacte helpery definovane v controller
     * @return  void
     */
    public function loadHelpers()
    {
        $this->helper('html');
        foreach ($this->controller->helpers as $helper) {
            $this->helper($helper);
        }
    }


    /**
     * Ulozi do seznamu promennych pro sablonu
     * Probiha s kontrolou ochrany promenne
     * @param   string  jmeno promenne
     * @param   mixed   hodnota promenne
     * @param   bool    bude promenna chranena
     * @return  void
     */
    public function set($name, $value, $protected = true)
    {
        if (empty($name))
            throw new Exception('Nelze nastavit hodnotu nejmenne promenne!');

        if (isset($this->protected[$name]))
            throw new Exception("Nelze nastavit novou hodnotu chranene promenne \$$name!");

        if ($protected === true) {
            $this->protected[$name] = true;
        }

        $this->vars[$name] = $value;
    }



    /**
     * Ulozi do seznamu promennych pro sablonu
     * Probiha bez kontroly ochrany
     * @param   string  jmeno promenne
     * @param   mixed   hodnota promenne
     * @param   bool    bude promenna chranena
     * @return  void
     */
    public function reset($name, $value, $protected = true)
    {
        if (empty($name))
            throw Exception('Nelze nastavit hodnotu nejmenne promenne!');

        if ($protected === true) {
            $this->protected[$name] = true;
        } else {
            unset($this->protected[$name]);
        }

        $this->vars[$name] = $value;
    }



    /**
     * Je nastavena promenna
     * @param   string  jmenno promenne
     * @return  boll
     */
    public function __isset($name)
    {
        return isset($this->vars[$name]);
    }



    /**
     * Smaze promennou
     * @param   string  jmeno promenne
     * @return  void
     */
    public function __unset($name)
    {
        if (isset($this->protected[$name]))
            throw new Exception("Nelze smazat hodnotu chranene promenne \$$name!");

        unset($this->vars[$name]);
    }



    /**
     * Ulozi do seznamu promennych pro sablonu
     * @param   string  jmeno promenne
     * @param   mixed   hodnota promenne
     * @return  void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value, false);
    }



    /**
     * Vrati hodnotu z promennych pro sablonu
     * @param   string  jmeno promenne
     * @return  mixed
     */
    public function __get($name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        } else {
            throw new Exception("Neexistujici promenna \$$name!");
        }
    }



    /**
     * Vyrenderuje view sablonu
     * @return  void
     */
    public function render()
    {
        ob_start();
        $this->viewPath = $this->viewPathFactory();
        return $this->parse($this->viewPath, $this->vars);
    }



    /**
     * Vytvori cestu k view sablone
     * @return  string
     */
    protected function viewPathFactory()
    {
        $app = Application::getInstance();

        if (!$app->error) {
            $view = Inflector::viewFile($this->ext, $this->viewName, Router::$namespace, $this->themeName, Router::$controller, !empty(Router::$service));

            if (file_exists($app->getPath() . $view)) {
                return $app->getPath() . $view;
            } else {
                throw new ApplicationException('view', $view);
            }
        } else {
            $appView = $app->getPath() . Inflector::errorViewFile($this->ext, $this->viewName, '');
            $coreView = $app->getCorePath() . Inflector::errorViewFile('phtml', $this->viewName, '');

            if (file_exists($appView)) {
                return $appView;
            } elseif (file_exists($coreView)) {
                return $coreView;
            } else {
                die("Haefko: chyby systemovy soubor $coreView!");
            }
        }
    }



    /**
     * Parsuje sablonu
     * @param   string  cesta k sablone
     * @param   array   promenne
     * @return  string
     */
    protected function parse($parsedFile, $parsedVars)
    {
        extract($parsedVars);
        $controller = $this->controller;
        $base = $this->base;

        include $parsedFile;
        return ob_get_clean();
    }



}