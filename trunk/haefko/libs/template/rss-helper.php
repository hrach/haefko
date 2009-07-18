<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
 * @subpackage  Templates
 */


require_once dirname(__FILE__) . '/../object.php';


class RssHelper extends Object
{

	/** @var array */
	protected $pairs = array(
		'published' => 'pubDate',
	);


	/**
	 * Returns formatted date in RFC822 format
	 * @param int|string|null $time timestamp or arg for strtotime
	 * @return string
	 */
	public function date($time = null)
	{
		$time = $time == null ? time() : (is_int($time) ? $time : strtotime($time));
		return gmdate(DATE_RFC2822, $time);
	}


	/**
	 * Returns tag
	 * @param string $tag tag name
	 * $param string $content tag content
	 * @return string
	 */
	public function rssTag($tag, $content)
	{
		return "<$tag>" . htmlspecialchars($content) . "</$tag>\n";
	}


	/**
	 * Wrapper for pubDate tag
	 * @param int|string|null $time timestamp or arg for strtotime
	 * @return string
	 */
	public function pubDate($time)
	{
		return $this->rssTag('pubDate', $this->date($time));
	}


	/**
	 * Wrapper for title tag
	 * @param string $content
	 * @return string
	 */
	public function title($content)
	{
		return $this->rssTag('title', strip_tags($content));
	}


	/**
	 * Call interface
	 * @param string $name method name
	 * @param array $args
	 * @return string
	 */
	public function __call($name, $args)
	{
		if (isset($this->pairs[$name]))
			return call_user_func_array(array($this, $this->pairs[$name]), $args);
		else
			return $this->rssTag($name, @$args[0]);
	}


}