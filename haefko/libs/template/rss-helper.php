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
	 * @param int|null $time timestamp
	 * @return string
	 */
	public function date($time = null)
	{
		$time = $time == null ? time() : (is_int($time) ? $time : strtotime($time));
		return gmdate(DATE_RFC2822, $time);
	}


	/**
	 *
	 */
	public function rssTag($tag, $content)
	{
		return "<$tag>" . htmlspecialchars($content) . "</$tag>\n";
	}


	public function pubDate($time)
	{
		return $this->rssTag('pubDate', $this->date($time));
	}

	public function title($content)
	{
		return $this->rssTag('title', strip_tags($content));
	}


	public function __call($name, $args)
	{
		if (isset($this->pairs[$name]))
			return call_user_func_array(array($this, $this->pairs[$name]), $args);
		else
			return $this->rssTag($name, @$args[0]);
	}


}