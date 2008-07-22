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
 * Js helper
 */
class JsHelper extends CustomHelper
{



    public $pathJs  = 'design/js/';
    public $pathCss = 'design/css/';

    protected $js;
    protected $files = array();
    protected $includeJs = array();
    protected $includeCss = array();

    private $linkBy;



    /**
     * Konstroktor - preddefinuje jmena souboru pro automaticke navteni plug-inu
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();

        $this->files = array(
            'jquery' => array('jquery.js'),
            'fancybox' => array('jquery.fancybox.js', 'jquery.fancybox.css'),
            'corner' => array('jquery.corner.js'),
            'calendar' => array('jquery.datepicker.js', 'date.js', 'jquery.datepicker.css'),
            'textarearesizer' => array('jquery.textarearesizer.js', 'jquery.textarearesizer.css'),
            'autocomplete' => array('jquery.autocomplete.js', 'jquery.autocomplete.css'),
            'validate' => array('jquery.validate.js'),
            'rater' => array('jquery.rater.js', 'jquery.rater.css'),
            'markitup' => array('jquery.markitup.js', 'jquery.markitup.css')
        );
    }



    /**
     * Nacten knihovny pro plug-in $name
     * @param   string  jmeno pluginu
     * @return  void
     */
    public function need($name)
    {
        $name = strtolower($name);
        if (!isset($this->files[$name]))
            die("Haefko: nepodporany script JsHelperu $name!");

        $this->js('jquery.js');

        foreach ($this->files[$name] as $item) {
            if (strpos($item, '.js') !== false)
                $this->js($item);
            else
                $this->css($item);
        }
    }



    /**
     * Prida html tagy pro include js $file
     * @param   strgin  jmeno js souboru
     * @return  bool
     */
    public function js($file)
    {
        if (!in_array($file, $this->includeJs)) {
            $this->includeJs[] = $file;
            return true;
        }

        return false;
    }



    /**
     * Prida html tagy pro include css $file
     * @param   strgin  jmeno js souboru
     * @return  bool
     */
    public function css($file)
    {
        if (!in_array($file, $this->includeCss)) {
            $this->includeCss[] = $file;
            return true;
        }

        return false;
    }



    /**
     * Vyrenderuje js kod
     * @return  string
     */
    public function render()
    {
        $code = null;

        foreach ($this->includeJs as $file)
            $code .= "\t" . $this->controller->view->html->js($this->pathJs . $file);

        foreach ($this->includeCss as $file)
            $code .= "\t" . $this->controller->view->html->css($this->pathCss . $file);

        if (!empty($this->js))
        $code .= "\t<script type=\"text/javascript\">\n\t//<![CDATA[\n"
              .  "\t$(document).ready(function() {\n\t\t\n{$this->js};\n\t});\n"
              .  "\t//]]>\n\t</script>";

        return $code;
    }



    public function raw($code)
    {
        if ($this->linkBy == '.')
            $this->js .= ";\n";

        $this->js .= "$code\n";
        $this->linkBy = '';
    }



    public function jquery($selector)
    {
        if (!empty($selector)) {
            if ($this->linkBy == '.')
                $this->js .= ";\n";

            $this->js .= "$('$selector')";
            $this->linkBy = '.';
        }

        return $this;
    }



    public function __call($name, $args)
    {
        if (isset($this->files[strtolower($name)]))
            $this->need($name);

        foreach ($args as & $val)
            $val = json_encode($val);

        $this->js .= "{$this->linkBy}$name(" . implode(', ', $args). ")";
        $this->linkBy = '.';

        return $this;
    }



    /**
     * Vyrenderuje js kod
     */
    public function __toString()
    {
        return $this->render();
    }



}