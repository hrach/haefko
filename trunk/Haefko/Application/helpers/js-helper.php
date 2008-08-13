<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.8
 * @package     Haefko
 */



require_once dirname(__FILE__) . '/../CustomHelper.php';



/**
 * Javascriptovı helper pro jednoduchou tvorbu; implementuje jQuery
 */
class JsHelper extends CustomHelper
{

	/** @var string Cesta k js a css scriptum */
	public $path  = '/design/jsTools/';

	/** @var string Javasriptovy kod */
	protected $js;

	/** @var array Definice souboru ktere se nahraji pri pouziti metody; $method => array($files) */
	protected $files = array();

	/** @var array Soubory js, ktere se includuji */
	protected $includeJs = array();

	/** @var array Soubory css, ktere se includuji */
	protected $includeCss = array();

	/** @var string */
	private $linkBy;



	/**
	 * Konstroktor - preddefinuje jmena souboru pro automaticke navteni plug-inu
	 * @return  void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->path = Config::read('jsHelper.path', $this->path);
		$this->files = array(
			'jquery' => array('jquery.js'),
			'corner' => array('jquery.corner.js'),
			'datepicker' => array('jquery.datePicker.js', 'date.js', 'datePicker.css'),
			'markitup' => array('jquery.markitup.js'),
			'textarearesizer' => array('jquery.textarearesizer.js', 'textarearesizer.css'),
			'shadowbox' => array('shadowbox.js'),
			'hfvalidate' => array('jquery.hfvalidate.js'),
		);

		$this->files = array_merge($this->files, Config::read('jsHelper.files', array()));
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
	 * Vyrenderuje js kod pro hlavicku
	 * @return  string
	 */
	public function render()
	{
		$code = '';

		foreach ($this->includeJs as $file)
			$code .= "\t" . $this->controller->view->html->js($this->path . $file);

		foreach ($this->includeCss as $file)
			$code .= "\t" . $this->controller->view->html->css($this->path . $file);

		if (!empty($this->js))
		$code .= "\t<script type=\"text/javascript\">\n\t//<![CDATA[\n"
			  .  "\t$(document).ready(function() {\n\t\t\n{$this->js};\n\t});\n"
			  .  "\t//]]>\n\t</script>";

		return $code;
	}



	/**
	 * Prida surovy javasriptovy kod
	 * @param   string  kod
	 * @return  void
	 */
	public function raw($code)
	{
		if ($this->linkBy == '.')
			$this->js .= ";\n";

		$this->js .= "$code\n";
		$this->linkBy = '';
	}



	/**
	 * Selector
	 * @param   string  selector
	 * @return  JsHelper
	 */
	public function jquery($selector)
	{
		$this->need('jquery');

		if ($this->linkBy == '.')
			$this->js .= ";\n";

		$this->js .= "$('$selector')";
		$this->linkBy = '.';

		return $this;
	}



	/**
	 * Magic method
	 * @param   string  jmeno metody
	 * @param   array   argumenty metody
	 * @return  JsHelper
	 */
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