<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko
 */


 require_once dirname(__FILE__) . '/object.php';


class Html extends Object
{


	/** @var array One tag elements */
	public static $emptyEl = array('img', 'meta', 'input', 'meta', 'area', 'base', 'col', 'link', 'param', 'frame', 'embed');



	/** @var string */
	public $prepend;

	/** @var string */
	public $append;

	/** @var string Tag's name*/
	protected $tag;

	/** @var bool */
	protected $isEmpty = false;

	/** @var array */
	protected $content = array();

	/** @var array */
	protected $attrs = array(
		'class' => array()
	);


	/**
	 * Html object factory
	 * @param   string   tag name
	 * @param   string   content
	 * @param   array    attributes
	 * @return  Html
	 */
	public static function el($tag = null, $text = null, $attrs = array(), $isEmpty = null)
	{
		$el = new Html();
		$el->tag = $tag;
		$el->attrs = $attrs;
		$el->setText($text);

		$el->attrs['class'] = (array) $el->attrs['class'];

		if ($isEmpty === null)
			$el->isEmpty = in_array($el->tag, self::$emptyEl);
		else
			$el->isEmpty = $isEmpty;

		return $el;
	}


	/**
	 * Constructor
	 */
	private function __construct()
	{}


	/**
	 * Overloaded attributes's setter
	 * @param   string   attribut name
	 * @param   array    attribut value
	 * @return  Html
	 */
	public function __call($name, $args)
	{
		# unset attribut
		if (empty($args[0]))
			$this->attrs[$name] = null;
		# add to array attribut
		elseif (is_array($this->attrs[$name]))
			$this->attrs[$name][] = $args[0];
		# add to attribut
		elseif ($args[1] === true)
			$this->attrs[$name] .= $args[0];
		# set attribut
		else
			$this->attrs[$name] = $args[0];

		return $this;
	}


	/**
	 * Overloaded attribut's setter
	 * @return  void
	 */
	public function __set($name, $value)
	{
		$this->attrs[$name] = $value;
	}


	/**
	 * Overloaded attribut's getter
	 * @return  mixed
	 */
	public function __get($name)
	{
		return $this->attrs[$name];
	}


	/**
	 * Overloaded attribut's unsetter
	 * @return  void
	 */
	public function __unset($name)
	{
		unset($this->attrs[$name]);
	}


	/**
	 * Sets attributes
	 * @param   array
	 * @return  Html
	 */
	public function setAttrs($attrs)
	{
		foreach ((array) $attrs as $name => $value)
			$this->$name($value);

		return $this;
	}


	/**
	 * Sets the html content
	 * @param   string  html content
	 * @return  Html
	 */
	public function setHtml($value)
	{
		$this->content[] = $value;
		return $this;
	}


	/**
	 * Sets the text content
	 * @param   string  text content
	 * @return  Html
	 */
	public function setText($value)
	{
		$this->content[] = htmlspecialchars($value);
		return $this;
	}


	/**
	 * Clears content
	 * @return  Html
	 */
	public function clearContent()
	{
		$this->content = array();
		return $this;
	}


	/**
	 * Renders element's start tag + content + end tag
	 * @return  string
	 */
	public function render()
	{
		$s = $this->startTag();

		if ($this->isEmpty)
			return $s;

		foreach ($this->content as $node) {
			if ($node instanceof Html)
				$s .= $node->render();
			else
				$s .= $node;
		}

		$s .= $this->endTag();

		return $s;
	}


	/**
	 * Renders start tag
	 * @return  string
	 */
	public function startTag()
	{
		if ($this->tag == '')
			return '';

		$s = "{$this->prepend}<{$this->tag}";
		foreach ((array) $this->attrs as $name => $value) {
			if (is_array($value))
				$value = implode(' ', $value);

			if ($value === '' || $value === null)
				continue;

			$s .= " $name=\"" . htmlspecialchars($value) . '"';
		}

		if ($this->isEmpty)
			return "$s/>{$this->append}\n";
		else
			return "$s>";
	}


	/**
	 * Render end tag
	 * @return  string
	 */
	public function endTag()
	{
		if (empty($this->tag))
			return '';

		return "</{$this->tag}>{$this->append}";
	}


	/**
	 * Toggles class
	 * @param   string  css class
	 * @return  bool
	 */
	public function toggleClass($class)
	{
		if (in_array($class, $this->attrs['class'])) {
			$this->attrs['class'] = array_diff($this->attrs['class'], array($class));
			return false;
		} else {
			$this->attrs['class'][] = $class;
			return true;
		}
	}


}