<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://hf.programujte.com
 * @version     0.6 alfa
 * @package     HF
 */



class Html implements ArrayAccess
{



    public static $emptyElements = array('img', 'meta', 'input', 'meta', 'area', 'base', 'col', 'link', 'param', 'frame', 'embed');

    private static $conditions = array();



    /**
     * Vrati instanci Html
     * @param   string  tag
     * @return  Html
     */
    public static function element($tag)
    {
        $el = new Html();
        $el->setTag($tag);

        return $el;
    }



    /**
     * Vytvori HTML odkaz
     * Pokud neuvedete title, pouzije se jako text samotne URL
     * @param   string  url
     * @param   string  text odkazu
     * @param   array   atributy
     * @param   bool    je text odkazu html
     * @return  string
     */
    public static function link($url, $title = null, array $attrs = array(), $escape = false)
    {
        $el = self::element('a');

        $el['href'] = $url;
        $el->setAttributes($attrs);

        if (is_null($title)) {
            $el->setContent($url);
        } else {
            $el->setContent($title, $escape);
        }

        return $el->render();
    }



    public static function condition($name, $content)
    {
        if (isset(self::$conditions[$name])) {
             return $content;
        } else {
             return;
        }
    }



    public static function activate($name)
    {
        self::$conditions[$name] = true;
    }



    public static function css($name, $media = 'screen')
    {
        $el = Html::element('link');
        $el['rel'] = 'stylesheet';
        $el['type'] = 'text/css';
        $el['href'] = Application::getInstance()->controller->view->base . $name;
        $el['media'] = $media;

        return $el->render();
    }



    public static function js($name)
    {
        $el = Html::element('script');
        $el['type'] = 'text/javascript';
        $el['src'] = Application::getInstance()->controller->view->base . $name;

        return $el->render();
    }



    public static function rss($name, $title = 'RSS')
    {
        $el = Html::element('link');
        $el['rel'] = 'alternate';
        $el['type'] = 'application/rss+xml';
        $el['href'] = $name;
        $el['title'] = $title;

        return $el->render();
    }



    public static function icon($name)
    {
        $el = Html::element('link');
        $el['rel'] = 'shortcut icon';
        $el['href'] = Application::getInstance()->controller->view->base . $name;

        return $el->render();
    }



    public static function encoding($code = 'UTF-8')
    {
        $el = Html::element('meta');
        $el['http-equiv'] = 'Content-type';
        $el['content'] = "text/html; charset=$code";

        return $el->render();
    }



    public static function title($title = null)
    {
        $el = Html::element('title');
        if (is_null($title)) {
            $el->setContent(Application::getInstance()->controller->view->title);
        } else {
            $el->setContent($title);
        }

        return $el->render();
    }



    private $name;
    private $attrs = array();
    private $content;
    private $empty;



    /**
     * Nastavi tag
     * @param   string  tag
     * @param   bool    je tag neparovy
     * @return  void
     */
    public function setTag($elementName, $empty = null)
    {
        $this->name = $elementName;
        $this->empty = is_null($empty) ? in_array($elementName, self::$emptyElements) : (bool) $empty;
    }



    /**
     * Vrati jmeno tagu
     * @return  string
     */
    public function getTag()
    {
        return $this->name;
    }



    /**
     * Nastavi atribut
     * @param   string  jmeno atributu
     * @param   string  hodnota
     * @return  void
     */
    public function setAttribute($name, $value)
    {
        $this->attrs[$name] = $value;
    }



    /**
     * Nastavi hromadne atributy
     * @param   array   pole s atributy $name => $value
     * @return  void
     */
    public function setAttributes(array $attrs)
    {
        foreach ($attrs as $name => $value) {
            $this->attrs[$name] = $value;
        }
    }



    /**
     * Vrati hondotu atributu $name
     * @param   string  jmeno atributu
     * @return  string
     */
    public function getAttribute($name)
    {
        return $this->attrs[$name];
    }



    /**
     * Nastavi obsah elementu
     * @param   string  obsah
     * @param   bool    escapovat obsah
     * @return  void
     */
    public function setContent($value, $escape = false)
    {
        if (!$escape) {
            $this->content = htmlspecialchars($value);
        } else {
            $this->content = $value;
        }
    }



    /**
     * Vrati obsah elementu
     * @return  string
     */
    public function getContent()
    {
        return $this->content;
    }



    /**
     * Vyrenderuje cely element
     * @return  string
     */
    public function render()
    {
        return $this->renderStart() . $this->getContent() . $this->renderEnd();
    }



    /**
     * Vygeneruje pocatecni tag
     * @return  string
     */
    public function renderStart()
    {
        $string = "<{$this->name}";

        foreach ($this->attrs as $name => $value) {
            $string .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        }

        if ($this->empty) {
            $string .= "/>\n";
        } else {
            $string .= '>';
        }

        return $string;
    }



    /**
     * Vyrenderuje koncovy tag (pouze pokud je element parovy)
     * @return  string
     */
    public function renderEnd()
    {
        if (!$this->empty) {
            return "</{$this->name}>\n";
        }
    }



    public function offsetSet($key, $value)
    {
        $this->attrs[$key] = $value;
    }



    public function offsetGet($key)
    {
        if (isset($this->attrs[$key])) {
            return $this->attrs[$key];
        }

        return false;
    }



    public function offsetUnset($key)
    {
        if (isset($this->attrs[$key])) {
            unset($this->attrs[$key]);
        }
    }



    public function offsetExists($key)
    {
        return isset($this->attrs[$key]);
    }



    public function __toString()
    {
        return $this->render();
    }



}