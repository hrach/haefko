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



require_once dirname(__FILE__) . '/../CustomHelper.php';



/**
 * Js helper
 */
class JsHelper extends CustomHelper
{



    public $base = 'design/';
    public $pathJs = 'js/';
    public $pathCss = 'css/';

    protected $filesCss = array();
    protected $filesJs = array('jquery.js');
    protected $inline = array();



    public function raw($line)
    {
        $this->inline[] = $line;
    }



    public function js($file)
    {
        if (!in_array($file, $this->filesJs))
            $this->filesJs[] = $file;
    }



    public function css($file)
    {
        if (!in_array($file, $this->filesCss))
            $this->filesCss[] = $file;
    }



    public function __toString()
    {
        $ret = '';

        foreach ($this->filesJs as $file)
            $ret .= "\t" . $this->controller->view->html->js($this->base . $this->pathJs . $file);

        foreach ($this->filesCss as $file)
            $ret .= "\t" . $this->controller->view->html->css($this->base . $this->pathCss . $file);

        if (!empty($this->inline))
        $ret .= "\t<script type=\"text/javascript\">\n\t//<![CDATA[\n"
             .  "\t\t$(document).ready(function() {\n\t\t"
             .  implode("\n\t\t", $this->inline)
             .  "\n\t});\n"
             .  "\t//]]>\n\t</script>";

        return $ret;
    }



    public function toJsArray(array $array)
    {
        array_walk($array, 'htmlentities');
        return "['" . join("', '", $array) . "']";
    }



    /* ================== jQuery methods ================== */



    public function datepicker($name = null, $options = null)
    {
        $this->js('jquery.datepicker.js');
        $this->js('date.js');
        $this->css('jquery.datepicker.css');
        if (!empty($name))
            $this->raw("$('$name').datePicker($options);");
    }



    public function fancybox($name = null, $options = null)
    {
        $this->js('jquery.fancybox.js');
        $this->css('jquery.fancybox.css');
        if (!empty($name))
            $this->raw("$('$name').fancybox($options);");
    }



    public function corner($name = null, $options = null)
    {
        $this->js('jquery.corner.js');
        if (!empty($name))
            $this->raw("$('$name').corner($options);");
    }



    public function resizer($name = null)
    {
        $this->js('jquery.textarearesizer.js');
        $this->css('jquery.textarearesizer.css');
        if (!empty($name))
            $this->raw("$('$name').TextAreaResizer();");
    }



    public function autocomplete($name = null, $options = array())
    {
        $options = $this->toJsArray($options);
        $this->js('jquery.autocomplete.js');
        $this->css('jquery.autocomplete.css');
        if (!empty($name))
            $this->raw("$('$name').autocomplete($options);");
    }



}