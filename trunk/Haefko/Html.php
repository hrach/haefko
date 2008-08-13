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



class Html implements ArrayAccess
{



    public static $emptyElements = array('img', 'meta', 'input', 'meta',
                                         'area', 'base', 'col', 'link',
                                         'param', 'frame', 'embed');



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


    private $name;
    private $attrs = array();
    private $content;
    private $empty;
    private $classes = array();



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
            if ($name == 'class')
                $this->addClass($value);
            else
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
        if (!$escape)
            $this->content = htmlspecialchars($value);
        else
            $this->content = $value;
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
        if ($this->empty)
            return $this->renderStart();
        else
            return $this->renderStart() . $this->getContent() . $this->renderEnd();
    }



    /**
     * Vygeneruje pocatecni tag
     * @return  string
     */
    public function renderStart()
    {
        $string = "<{$this->name}";

        $this->attrs['class'] = $this->getClasses();

        foreach ($this->attrs as $name => $value) {
            if (!empty($value) || $value == '0')
                $string .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        }

        if ($this->empty)
            $string .= "/>\n";
        else
            $string .= '>';

        return $string;
    }



    /**
     * Vyrenderuje koncovy tag (pouze pokud je element parovy)
     * @return  string
     */
    public function renderEnd()
    {
        if (!$this->empty)
            return "</{$this->name}>" . ($this->name == 'a' ? '' : "\n");
    }



    /**
     * Prida tridu
     * @param   string  jmeno tridy
     * @return  void
     */
    public function addClass($names)
    {
        if (!is_array($names))
            $names = func_get_args();

        foreach ($names as $name) {
            if (!empty($name))
                $this->classes[$name] = true;
        }
    }



    /**
     * Odebere tridu
     * @param   string  jmeno tridy
     * @return  void
     */
    public function removeClass($name)
    {
        unset($this->classes[$name]);
    }



    /**
     * Vrati tridy
     * @return  string
     */
    public function getClasses()
    {
        return implode(' ', array_keys($this->classes));
    }



    public function offsetSet($key, $value)
    {
        $this->attrs[$key] = $value;
    }



    public function offsetGet($key)
    {
        if (isset($this->attrs[$key]))
            return $this->attrs[$key];

        return false;
    }



    public function offsetUnset($key)
    {
        if (isset($this->attrs[$key]))
            unset($this->attrs[$key]);
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