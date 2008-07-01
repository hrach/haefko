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



require_once dirname(__FILE__) . '/View.php';



/**
 * Trida View obstarava nacitani view a layoutu
 */
class LayoutView extends View
{



    private $layoutPath;
    private $layoutName = 'layout';



    /**
     * Konstruktor
     */
    public function __construct()
    {
        parent::__construct();
    }



    /**
     * Nastavi jmeno layout sablony
     * Pokud zadate false, nepouzije se zadna sablona
     * @param   string  jemeno sablony
     * @return  void
     */
    public function layout($layoutName)
    {
        $this->layoutName = $layoutName;
    }



    /**
     * Vrati jmeno layout sablony, bez pripony
     * @return  string
     */
    public function getLayout()
    {
        return $this->layoutName;
    }



    /**
     * Vrati cestu k view layoutu
     * @return  string
     */
    public function getLayoutPath()
    {
        return $this->layoutPath;
    }



    /**
     * Naètení externího kodu
     * @param   string  jmeno souboru bez pripony
     * @return  void
     */
    public function renderElement($name)
    {
        $file = $this->controller->app->getPath() . Inflector::elementFile($this->ext, $name);

        if (file_exists($file)) {
            extract($this->vars);
            $base = $this->base;
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
     * Vytvori tlacitko v zavislosti na systemovem routingu s moznosti js potvrzeni
     * @param   string  url
     * @param   string  text odkazu
     * @param   bool    js potvrzeni
     * @param   array   pole s html atributy
     * @param   bool    je text tlacitka html
     * @return  string
     */
    public function button($url, $title, $confirm = false, array $attrs = array(), $html = false)
    {
        $app = Application::getInstance();
        $url = call_user_func_array(array($app->controller, 'url'), (array) $url);

        return $this->html->button($url, $title, $confirm, $attrs, $html);
    }



    /**
     * Vyrenderuje stranku z view a layoutu
     * @return  void
     */
    public function render()
    {
        $this->vars['content'] = parent::render();
        $this->layoutPath = $this->layoutPathFactory();

        if ($this->layoutName === false) {
            return $this->vars['content'];
        } else {
            return $this->parse($this->layoutPath, $this->vars);
        }
    }



    /**
     * Vytvori cestu k layout sablone
     * @return  string
     */
    protected function layoutPathFactory()
    {
        $app = Application::getInstance();

        $x = -1;
        $layouts = array(
            $app->getPath() . Inflector::layoutFile($this->ext, $this->layoutName, Router::$namespace, $this->themeName),
            $app->getCorePath() . 'views/' . Strings::dash($this->layoutName) . '.phtml',
            $app->getPath() . 'views/layout' . $this->ext,
            $app->getCorePath() . 'views/layout.phtml'
        );

        foreach ($layouts as $layout) {
            if (file_exists($layout)) {
                return $layout;
            }
        }

        $this->layoutName = false;
    }



}