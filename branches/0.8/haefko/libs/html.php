<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Libs
 */


require_once dirname(__FILE__) . '/object.php';


class Html extends Object
{


	/** @var array One tag elements */
	public static $emptyEl = array('img', 'meta', 'input', 'meta', 'area', 'base', 'col', 'link', 'param', 'frame', 'embed');

	/** @var string Tag's name*/
	protected $tag;

	/** @var bool */
	protected $isEmpty = false;

	/** @var array */
	protected $content = array();

	/** @var string */
	protected $prepend = array();

	/** @var string */
	protected $append = array();

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
		$el->setText($text);

		if (!empty($attrs))
			$el->attrs = (array) $attrs;

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
		elseif (isset($this->attrs[$name]) && is_array($this->attrs[$name]))
			$this->attrs[$name][] = $args[0];
		# add to attribut
		elseif (isset($this->attrs[$name]) && isset($args[1]) && $args[1] === true)
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
	 * @throws  Exception
	 * @return  mixed
	 */
	public function __get($name)
	{
		if (!array_key_exists($name, $this->attrs))
			throw new Exception("Undefined attribut $name.");

		return $this->attrs[$name];
	}


	/**
	 * Overloaded attribut's unsetter
	 * @throws  Exception
	 * @return  void
	 */
	public function __unset($name)
	{
		if (!array_key_exists($name, $this->attrs))
			throw new Exception("Undefined attribut $name.");

		unset($this->attrs[$name]);
	}


	/**
	 * Sets tag name
	 * @param   string    tag name
	 * @return  Html
	 */
	public function setTag($name)
	{
		$this->tag = $name;
		return $this;
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
	 * @param   mixed     html content
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
	public function clear($prepend = false, $append = false)
	{
		$this->content = array();
		if ($prepend)
			$this->prepend = array();

		if ($append)
			$this->append = array();

		return $this;
	}


	/**
	 * Prepends content before tag
	 * @param   mixed     content
	 * @return  string
	 */
	public function prepend($value, $html = true)
	{
		if (!$html)
			$value = htmlspecialchars($value);

		$this->prepend[] = $value;
		return $this;
	}


	/**
	 * Appends content before tag
	 * @param   mixed     content
	 * @return  string
	 */
	public function append($value, $html = true)
	{
		if (!$html)
			$value = htmlspecialchars($value);

		$this->append[] = $value;
		return $this;
	}


	/**
	 * Renders element's start tag + content + end tag
	 * @param   bool     append new line
	 * @return  string
	 */
	public function render($newLine = false)
	{
		$s = $this->startTag();

		if ($this->isEmpty)
			return $s . ($newLine ? "\n" : '');

		$s .= $this->renderString($this->content);
		$s .= $this->endTag();

		return $s . ($newLine ? "\n" : '');
	}


	/**
	 * Renders start tag
	 * @return  string
	 */
	public function startTag()
	{
		if ($this->tag == '')
			return '';

		$s = $this->renderString($this->prepend)
		   . "<{$this->tag}";

		foreach ((array) $this->attrs as $name => $value) {
			if (is_array($value))
				$value = implode(' ', $value);

			if ($value === '' || $value === null)
				continue;

			$s .= " $name=\"" . htmlspecialchars($value) . '"';
		}

		if ($this->isEmpty)
			return "$s/>" . $this->renderString($this->append) . "\n";
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

		return "</{$this->tag}>" . $this->renderString($this->append);
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


	/**
	 * Transforms array to string
	 * @param  array     array of nodes
	 * @return string
	 */
	protected function renderString($content)
	{
		$s = '';
		foreach ($content as $node) {
			if ($node instanceof Html)
				$s .= $node->render(true);
			else
				$s .= $node;
		}

		return $s;
	}


}