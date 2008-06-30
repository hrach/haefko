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
        array_walk($array, 'htmlentities', ENT_QUOTES);
        return "['" . join("', '", $array) . "']";
    }



    /* ================== jQuery methods ================== */



    public function datepicker($name, $options = null)
    {
        $this->js('jquery.datepicker.js');
        $this->js('date.js');
        $this->css('jquery.datepicker.css');
        $this->raw("$('$name').datePicker($options);");
    }



    public function fancybox($name, $options = null)
    {
        $this->js('jquery.fancybox.js');
        $this->css('jquery.fancybox.css');
        $this->raw("$('$name').fancybox($options);");
    }



    public function corner($name, $options = null)
    {
        $this->js('jquery.corner.js');
        $this->raw("$('$name').corner($options);");
    }



    public function resizer($name)
    {
        $this->js('jquery.textarearesizer.js');
        $this->css('jquery.textarearesizer.css');
        $this->raw("$('$name').TextAreaResizer();");
    }



}