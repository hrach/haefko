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

    /** @var CustomController */
    public $controller;

    /** @var string Relativni cesta vztamo k web-rootu serveru */
    public $base;

    /** @var string Pripona view */
    public $ext = 'phtml';

    /** @var array Promenne pro view */
    protected $vars = array();

    /** @var array Seznam chranenych promennych */
    protected $protected = array();

    /** @var string Cesta ke view */
    protected $viewPath;

    /** @var string Jmeno view */
    protected $viewName;

    /** @var string Jmeno tematu */
    protected $themeName;



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
     * @return  void
     */
    public function view($viewName)
    {
        $this->viewName = $viewName;
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
        if (is_null($var))
            $var = strtolower($name);

        if (!isset($this->$var)) {
            $class = Inflector::helperClass($name);

            if (!class_exists($class))
                throw new ApplicationException('missing-file', Inflector::helperFile($class));

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
        foreach ($this->controller->helpers as $helper)
            $this->helper($helper);
    }



    /**
     * Načtení externího kodu
     * @param   string  jmeno souboru bez pripony
     * @return  void
     */
    public function renderSnippet($name)
    {
        $file = $this->controller->app->path . Inflector::elementFile($this->ext, $name);

        if (file_exists($file)) {
            extract($this->vars);
            $controller = $this->controller;
            include $file;
        } else {
            die("Haefko: nenalezena sablona elementu $file!");
        }
    }



    /**
     * Vytvori odkaz v zavislosti na systemovem routingu
     * @param   string  url
     * @param   string  text odkazu
     * @param   array   pole s html atributy
     * @param   bool    je text odkazu html
     * @return  string
     */
    public function link($url, $title, array $attrs = array(), $html = false)
    {
        $app = Application::getInstance();
        $url = call_user_func_array(array($app->controller, 'url'), (array) $url);

        return $this->html->link($url, $title, $attrs, $html);
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

        if ($protected === true)
            $this->protected[$name] = true;

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

        if ($protected === true)
            $this->protected[$name] = true;
        else
            unset($this->protected[$name]);

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
        if (isset($this->vars[$name]))
            return $this->vars[$name];
        else
            throw new Exception("Neexistujici promenna \$$name!");
    }



    /**
     * Vyrenderuje view sablonu
     * @return  void
     */
    public function render()
    {
        if ($this->viewName === false)
            $this->viewPath = false;
        else
            $this->viewPath = $this->viewPathFactory();

        if ($this->viewPath === false) {
            return;
        } else {
            call_user_func(array($this->controller, 'prepareView'));
            return $this->parse($this->viewPath, $this->vars);
        }
    }



    /**
     * Vytvori cestu k view sablone
     * @return  string
     */
    protected function viewPathFactory()
    {
        $app = Application::getInstance();

        if (!$app->error) {

            if ($this->controller->ajax) {
                $ajaxView = Inflector::viewFile("ajax.{$this->ext}", $this->viewName, Router::$namespace, $this->themeName, Router::$controller, !empty(Router::$service));

                if (file_exists($app->path . $ajaxView))
                    return $app->path . $ajaxView;
                else
                    return false;
            } else {
                $view = Inflector::viewFile($this->ext, $this->viewName, Router::$namespace, $this->themeName, Router::$controller, !empty(Router::$service));

                if (file_exists($app->path . $view))
                    return $app->path . $view;
                else
                    throw new ApplicationException('missing-view', $view);
            }

        } else {

            $appView = $app->path . Inflector::errorViewFile($this->ext, $this->viewName, '');
            $coreView = $app->corePath  . 'Application/' . Inflector::errorViewFile('phtml', $this->viewName, '');

            if (file_exists($appView))
                return $appView;
            elseif (file_exists($coreView))
                return $coreView;
            else
                die("Haefko: chyby systemovy soubor $coreView!");

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

        include $parsedFile;
        return ob_get_clean();
    }



}