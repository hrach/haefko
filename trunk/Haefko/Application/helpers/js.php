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

    protected $defitions = array();
    protected $filesCss = array();
    protected $filesJs = array('jquery.js');
    protected $inline = array();



    public function __construct()
    {
        parent::__construct();

        $this->definitions = array(
            'jquery' => array('jquery.js'),
            'fancy' => array('jquery.fancybox.js', 'jquery.fancybox.css'),
            'corner' => array('jquery.corner.js'),
            'calendar' => array('jquery.datepicker.js', 'date.js', 'jquery.datepicker.css'),
            'resizer' => array('jquery.textarearesizer.js', 'jquery.textarearesizer.css'),
            'autocomplete' => array('jquery.autocomplete.js', 'jquery.autocomplete.css'),
            'validate' => array('jquery.validate.js'),
        );
    }



    public function need($name)
    {
        if (!isset($this->definitions[$name])) {
            die("Haefko: nepodporany script JsHelperu $name!");
        }

        foreach ($this->definitions[$name] as $item) {
            if (strpos($item, '.js') !== false) {
                $this->js($item);
            } else {
                $this->css($item);
            }
        }
    }



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
             .  "\t$(document).ready(function() {\n\t\t"
             .  implode("\n\t\t", $this->inline)
             .  "\n\t});\n"
             .  "\t//]]>\n\t</script>";

        return $ret;
    }



    /* ================== jQuery plug-in's methods ================== */



    public function calendar($name, $options = null)
    {
        $this->need('calendar');
        $this->raw("$('$name').datePicker($options);");
    }



    public function fancy($name, $options = null)
    {
        $this->need('fancy');
        $this->raw("$('$name').fancybox($options);");
    }



    public function corner($name, $options = null)
    {
        $this->need('corner');
        $this->raw("$('$name').corner($options);");
    }



    public function resizer($name)
    {
        $this->need('resizer');
        $this->raw("$('$name').TextAreaResizer();");
    }



    public function autocomplete($name, array $options)
    {
        $this->need('autocomplete');
        $options = toJsArray($options);
        $this->raw("$('$name').autocomplete($options);");
    }



}