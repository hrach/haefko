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



require_once dirname(__FILE__) . '/IView.php';
require_once dirname(__FILE__) . '/CustomView.php';



/**
 * Trida View obstarava nacitani view a layoutu
 */
class View extends CustomView implements IView
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
        extract($this->vars);

        $fileName   = $this->pathFactory('element', $name);
        $base       = $this->base;
        $controller = $this->controller;

        if (file_exists($fileName)) {
            include $fileName;
        }
    }



    /**
     * Vytvori odkaz v zavislosti na systemovem routingu
     * @param   string  url
     * @param   string  text odkazu
     * @param   array   pole s atributy
     * @param   bool    je text odkazu html
     * @return  string
     */
    public function link($url, $title, array $attrs = array(), $html = false)
    {
        $app = Application::getInstance();
        $url = call_user_func_array(array($app->controller, 'url'), (array) $url);

        return Html::link($url, $title, $attrs, $html);
    }



    /**
     * Vytvori tlacitko v zavislosti na systemovem routingu s moznosti js potvrzeni
     * @param   string  url
     * @param   string  text odkazu
     * @param   bool    js potvrzeni
     * @return  string
     */
    public function button($url, $title, $confirm = false)
    {
        $app = Application::getInstance();
        $url = call_user_func_array(array($app->controller, 'url'), (array) $url);

        return Html::button($url, $title, $confirm);
    }



    /**
     * Vyrenderuje stranku z view a layoutu
     * @return  void
     */
    public function render()
    {
        ob_start();

        $this->viewPath = $this->pathFactory('view');
        $this->vars['content'] = $this->parse($this->viewPath, $this->vars);

        if ($this->layoutName === false) {
            echo $this->vars['content'];
        } else {
            $this->layoutPath = $this->pathFactory('layout');
            echo $this->parse($this->layoutPath, $this->vars);
        }
    }



    /**
     * Vytvori cestu k view sablone
     * V pripade chyby vola prislusnou chybovou zpravu
     * @return  void
     */
    protected function pathFactory($type, $name = null)
    {
        $app = Application::getInstance();

        if (empty($this->themeName)) {
            $theme = '';
        } else {
            $theme = $this->themeName . '/';
        }

        switch ($type) {
            case 'element':
                return $app->getPath() . "views/" . $theme . $name . $this->ext;
            break;
            case 'view':
                return parent::pathFactory();
            break;
            case 'layout':
                $namespace = Router::$namespace;
                if (!empty($namespace)) $namespace .= '-';

                $x = -1;
                $layouts = array(
                    $app->getPath() . 'views/' . $theme . Strings::underscore($namespace . $this->layoutName) . $this->ext,
                    $app->getCorePath() . 'views/' . Strings::underscore($this->layoutName) . '.phtml',
                    $app->getPath() . 'views/layout' . $this->ext
                );

                foreach ($layouts as $layout) {
                    if (file_exists($layout)) {
                        return $layout;
                    }
                }

                return false;
            break;
        }
    }



    /**
     * Parsuje sablonu
     * @param   string  cesta k souboru
     * @param   array   pole s promennymi
     * @return  string
     */
    private function parse($parsedFile, $parsedVars)
    {
        extract($parsedVars);
        $controller = $this->controller;
        $base       = $this->base;

        include $parsedFile;
        return ob_get_clean();
    }



}