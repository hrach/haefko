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
            call_user_func(array($this->controller, 'prepareLayout'));
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

        $layouts = array(
            $app->path . Inflector::layoutFile($this->ext, $this->layoutName, Router::$namespace, $this->themeName),
            $app->path . Inflector::layoutFile($this->ext, $this->layoutName, '', ''),
            $app->corePath . 'Application/' . Inflector::layoutFile($this->ext, $this->layoutName, '', ''),
            $app->corePath . 'Application/' . Inflector::layoutFile('phtml', 'layout', '', ''),
        );

        foreach ($layouts as $layout) {
            if (file_exists($layout))
                return $layout;
        }

        $this->layoutName = false;
    }



}