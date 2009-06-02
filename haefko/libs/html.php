<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Libs
 */


require_once dirname(__FILE__) . '/object.php';


class Html extends Object
{


	/** @var array - Tags without pairs */
	public static $nonPairs = array('area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source');

	/** @var string - Tag's name*/
	protected $tag;

	/** @var bool */
	protected $hasPair = true;

	/** @var array */
	protected $content = array();

	/** @var string|Html */
	protected $prepend;

	/** @var string|Html */
	protected $append;

	/** @var array */
	protected $attrs = array(
		'href' => null,
		'action' => null,
		'method' => null,
		'type' => null,
		'name' => null,
		'id' => null,
		'class' => array()
	);


	/**
	 * Html object factory
	 * @param   string   tag name
	 * @param   string   content
	 * @param   array    attributes
	 * @return  Html
	 */
	public static function el($tag = null, $text = null, $attrs = array(), $hasPair = null)
	{
		$el = new Html();
		$el->setTag($tag, $hasPair);

		if (!empty($text))
			$el->setText($text);

		if (!empty($attrs))
			$el->setAttrs($attrs);

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
		if (empty($args[0])) {
			$this->attrs[$name] = null;
		# add to array attribut
		} elseif (isset($this->attrs[$name]) && is_array($this->attrs[$name])) {
			if (isset($args[1]) && $args[1] == true)
				$this->attrs[$name] = array_diff($this->attrs[$name], array($args[0]));
			else
				$this->attrs[$name][] = $args[0];
		# add to attribut
		} elseif (isset($this->attrs[$name]) && isset($args[1]) && $args[1] === true) {
			$this->attrs[$name] .= $args[0];
		# set attribut
		} else {
			$this->attrs[$name] = $args[0];
		}

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
	public function setTag($name, $hasPair = null)
	{
		$this->tag = $name;

		if ($hasPair === null)
			$this->hasPair = !in_array($this->tag, self::$nonPairs);
		else
			$this->hasPair = $hasPair;

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
	public function setHtml($value = null)
	{
		$this->content = array();
		return $this->addHtml($value);
	}


	/**
	 * Adds the html content
	 * @param   mixed     html content
	 * @return  Html
	 */
	public function addHtml($value = null)
	{
		if (!empty($value) || $value === 0)
			$this->content[] = $value;

		return $this;
	}


	/**
	 * Sets the text content
	 * @param   string  text content
	 * @return  Html
	 */
	public function setText($value = null)
	{
		$this->content = array();
		return $this->addText($value);
	}


	/**
	 * Adds the text content
	 * @param   string  text content
	 * @return  Html
	 */
	public function addText($value = null)
	{
		if (!empty($value) || $value === 0)
			$this->content[] = htmlspecialchars($value);

		return $this;
	}


	/**
	 * Prepends content before tag
	 * @param   mixed     content
	 * @return  string
	 */
	public function prepend($value = null)
	{
		$this->prepend = $value;
		return $this;
	}


	/**
	 * Appends content before tag
	 * @param   mixed     content
	 * @return  string
	 */
	public function append($value = null)
	{
		$this->append = $value;
		return $this;
	}


	/**
	 * Renders element's start tag + content + end tag
	 * @param   int      indent of block
	 * @return  string
	 */
	public function render($indent = null)
	{
		$r  = $this->startTag();
		$r .= $this->renderContent($indent);
		$r .= $this->endTag();

		if ($indent !== null)
			$r = "\n" . str_repeat("\t", $indent) . $r . ($indent - 1 < 1 ? '' : "\n") . str_repeat("\t", max(0, $indent - 1));

		return $r;
	}


	/**
	 * Renders start tag
	 * @return  string
	 */
	public function startTag()
	{
		if (empty($this->tag))
			return;

		return $this->renderPrepend() . '<' . $this->tag . $this->renderAttributes()
		     . ($this->hasPair ? '>' : '/>');
	}


	/**
	 * Render end tag
	 * @return  string
	 */
	public function endTag()
	{
		if (!$this->hasPair || empty($this->tag))
			return '';

		return '</' . $this->tag . '>' . $this->renderAppend();
	}


	/**
	 * Renders tag content
	 * @param  int     indent
	 * @return string
	 */
	protected function renderContent($indent = 0)
	{
		$s = '';
		foreach ($this->content as $node) {
			if ($node instanceof Html)
				$s .= $node->render(is_int($indent) ? $indent + 1 : null);
			else
				$s .= $node;
		}

		return $s;
	}


	/**
	 * Renders tag attributes
	 * @return  string
	 */
	protected function renderAttributes()
	{
		$r = '';	
		foreach ((array) $this->attrs as $key => $value) {
			if (is_array($value))
				$value = implode(' ', $value);

			if ($value === '' || $value === null || $value === false)
				continue;
			elseif ($value === true)
				$value = $key;

			$r .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
		}

		return $r;
	}


	/**
	 * Renders prepend
	 * @return  string
	 */
	protected function renderPrepend()
	{
		if (empty($this->prepend))
			return null;

		if ($this->prepend instanceof Html)
			return $this->prepend->render();
		else
			return $this->prepend;
	}


	/**
	 * Renders append
	 * @return  string
	 */
	protected function renderAppend()
	{
		if (empty($this->append))
			return null;

		if ($this->append instanceof Html)
			return $this->append->render();
		else
			return $this->append;
	}


	/**
	 * To string interface
	 * @return  string
	 */
	public function __toString()
	{
		return $this->render();
	}


}