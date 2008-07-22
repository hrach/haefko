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



require_once dirname(__FILE__) . '/../CustomHelper.php';



/**
 * Html helper
 */
class HtmlHelper extends CustomHelper
{



    protected $conditions = array();



    /**
     * Vytvori HTML odkaz
     * Pokud neuvedete title, pouzije se jako text samotne URL
     * @param   string  url
     * @param   string  text odkazu
     * @param   array   atributy
     * @param   bool    je text odkazu html
     * @return  string
     */
    public function link($url, $title = null, array $attrs = array(), $escape = false)
    {
        $el = Html::element('a');

        if (substr($url, 0, 4) == 'www.')
            $url = "http://$url";

        $el['href'] = $url;
        $el->setAttributes($attrs);

        if (is_null($title))
            $el->setContent($url);
        else
            $el->setContent($title, $escape);

        return $el->render();
    }



    /**
     * Vytvori HTML odkaz
     * Pokud neuvedete title, pouzije se jako text samotne URL
     * @param   string  url
     * @param   string  text odkazu
     * @param   bool    js potvrzeni
     * @param   array   html stributy
     * @param   bool    je text tlacitka html
     * @return  string
     */
    public function button($url, $title, $confirm = false, array $attrs = array(), $escape = false)
    {
        $el = Html::element('button');
        $el->setAttributes($attrs);
        $el->setContent($title, $escape);

        $el['type'] = 'button';
        $el['onclick'] = "document.location.href='$url'";

        if ($confirm !== false && !empty($confirm))
            $el['onclick'] = "if (confirm('$confirm')) {" . $el['onclick'] . "}";

        return $el->render();
    }



    public function isActive($name)
    {
        return isset($this->conditions[$name]);
    }



    public function activate($name)
    {
        $this->conditions[$name] = true;
    }



    /**
     * Vrati html tag pro externi css styl
     * @param   string  relativni cesta vztazmo k aplikaci
     * @param   string  media
     * @return  string
     */
    public function css($name, $media = 'screen')
    {
        $el = Html::element('link');
        $el['rel'] = 'stylesheet';
        $el['type'] = 'text/css';
        $el['href'] = $this->controller->view->base . $name;
        $el['media'] = $media;

        return $el->render();
    }



    /**
     * Vrati html tag pro externi js script
     * @param   string  relativni cesta vztazmo k aplikaci
     * @return  string
     */
    public function js($name)
    {
        $el = Html::element('script');
        $el['type'] = 'text/javascript';
        $el['src'] = $this->controller->view->base . $name;

        return $el->render();
    }



    /**
     * Vytvori tag odkazujici na rss kanal
     * @param   string  retezec pro CustomController::url()
     * @param   string  titulek
     * @return  string
     */
    public function rss($url, $title = 'RSS')
    {
        $el = Html::element('link');
        $el['rel'] = 'alternate';
        $el['type'] = 'application/rss+xml';
        $el['href'] = $this->controller->url($url);
        $el['title'] = $title;

        return $el->render();
    }



    /**
     * Vrati html tag pro fav-iconu
     * @param   string  relativni cesta vztazmo k aplikaci
     * @return  string
     */
    public function icon($name)
    {
        $el = Html::element('link');
        $el['rel'] = 'shortcut icon';
        $el['href'] = $this->controller->view->base . $name;

        return $el->render();
    }




    /**
     * Vrati html tag kodovani dokumentu
     * @param   string  kodovani
     * @return  string
     */
    public function encoding($code = 'UTF-8')
    {
        $el = Html::element('meta');
        $el['http-equiv'] = 'Content-type';
        $el['content'] = "text/html; charset=$code";

        return $el->render();
    }



    /**
     * Vrati html tag title s titulkem
     * V pripade ze titulek nezadate, pokusi se metody vytahnout z promennych view
     * @param   string  titulek
     * @param   string  suffix titlku
     * @return  string
     */
    public function title($title = null, $suffix = null)
    {
        $el = Html::element('title');
        if (is_null($title))
            $el->setContent($this->controller->view->title . $suffix);
        else
            $el->setContent($title . $suffix);

        return $el->render();
    }



    /**
     * Vypise html podpis frameworku
     * @return  string
     */
    public function powered()
    {
        echo 'Powered by <a href="http://haefko.programujte.com">Haefko</a>';
    }



}