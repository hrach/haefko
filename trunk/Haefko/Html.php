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


final class Html
{


	/** @var string */
	public $tag;

	/** @var bool */
	public $isEmpty = false;

	/** @var mixed */
	private $content;

	/** @var array */
	private $attrs = array();

	/** @var array Empty tags */
	private static $emptyElements = array(
		'img', 'meta', 'input', 'meta', 'area', 'base',
		'col', 'link', 'param', 'frame', 'embed'
	);


	/**
	 * Return Html object
	 * @param   string         tag name
	 * @param   array|string   attributes|content
	 * @return  Html
	 */
	public static function el($tag, $attrs = null)
	{
		$el = new Html();
		$el->tag = strtolower($tag);
		$el->isEmpty = in_array($el->tag, self::$emptyElements);

		if (is_array($attrs))
			$el->setAttributes($attrs);
		elseif (!empty($attrs))
			$el->setContent($attrs);

		return $el;
	}


	/**
	 * Set the array of attributes
	 * @param   array   attributes: $key => $value
	 * @return  void
	 */
	public function setAttributes($attrs)
	{
		foreach ($attrs as $name => $value) {
			if ($name == 'class') {
				if (is_array($value))
					$this->attrs['class'] = array_merge((array) $this->attrs['class'], $value);
				else
					$this->attrs['class'][] = $value;
			} else {
				$this->attrs[$name] = $value;
			}
		}
	}


	/**
	 * Set the content
	 * @param   string  content
	 * @param   bool    escape content
	 * @param   bool    escape content
	 * @return  void
	 */
	public function setContent($value, $escape = true, $append = false)
	{
		if ($escape)
			$value = htmlspecialchars($value);

		if ($append)
			$this->content .= $value;
		else
			$this->content = $value;
	}


	/**
	 * Return content
	 * @return  string
	 */
	public function getContent()
	{
		return $this->content;
	}


	/**
	 * Render start tag + content + end tag
	 * @param   int     tag's indent
	 * @return  string
	 */
	public function render($indent = 0)
	{
		$render = $this->startTag();

		if ($this->isEmpty)
			return $render;

		$render .= $this->getContent()
		        .  $this->endTag();

		return str_repeat("\t", $indent) . $render;
	}


	/**
	 * Render start tag
	 * @return  string
	 */
	public function startTag()
	{
		$tag = "<{$this->tag}";

		foreach ($this->attrs as $name => $value) {
			if ($value === null || $value === false)
				continue;

			if (is_array($value))
				$value = implode(' ', $value);

			$tag .= " $name=\"" . htmlspecialchars($value) . '"';
		}

		if ($this->isEmpty)
			return $tag . "/>\n";
		else
			return $tag . '>';
	}


	/**
	 * Render end tag
	 * @return  string
	 */
	public function endTag()
	{
		return "</{$this->tag}>" . ($this->tag != 'a' ? "\n" : '');
	}


	/**
	 * Magic method
	 */
	public function __set($key, $value)
	{
		$this->attrs[$key] = $value;
	}


	/**
	 * Magic method
	 */
	public function __get($key)
	{
		if (!isset($this->attrs[$key]))
			return null;

		return $this->attrs[$key];
	}


	/**
	 * Magic method
	 */
	public function __unset($key)
	{
		unset($this->attrs[$key]);
	}


	/**
	 * Magic method
	 */
	public function __isset($key)
	{
		return isset($this->attrs[$key]);
	}


	/**
	 * Magic method
	 */
	public function __toString()
	{
		return $this->render();
	}


}