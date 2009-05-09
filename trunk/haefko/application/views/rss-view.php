<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Application
 * @subpackage  View
 */


require_once dirname(__FILE__) . '/view.php';


class RssView extends View implements IView
{


	/** @var string Rss item */
	public $title;

	/** @var string Rss item */
	public $link;

	/** @var string Rss item */
	public $description;

	/** @var string Rss item */
	public $lang;

	/** @var string Rss item */
	public $copyright;

	/** @var array Rss item */
	public $image = array(
		'title' => null,
		'src' => null,
		'link' => null
	);

	/** @var string */
	public $ext = 'rss.php';

	/** @var array */
	protected $items = array();


	/**
	 * Returns rss item
	 * @return  RssViewItem
	 */
	public function item()
	{
		return new RssItem($this);
	}


	/**
	 * Renders template
	 * @return  string
	 */
	public function render()
	{
		# turn off layout and chenge loggin into firebug
		$this->layout(false);
		Config::write('Debug.logto', 'firebug');
		$content = parent::render();

		# send header
		Http::headerMimetype('application/rss+xml');

		# render layout
		return $this->renderFeed($content);
	}


	/**
	 * Renders feed
	 * @param   string    feed contents - items
	 * @return  void
	 */
	public function renderFeed($content)
	{
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		echo "<rss version=\"2.0\">\n"; 
		echo "<channel>\n";
			echo "\t<title>", htmlspecialchars($this->title), "</title>\n";
			echo "\t<description>", htmlspecialchars($this->description), "</description>\n";
			echo "\t<link>", $this->controller->url($this->link, null, true), "</link>\n";
			echo "\t<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
			echo "\t<lastBuildDate>", $this->date(), "</lastBuildDate>\n";
			echo "\t<generator>Haefko - your php5 framework</generator>\n";

			if (isset($this->image['title']) && isset($this->image['src']) && isset($this->image['link'])) {
				echo "\t<image>\n";
					echo "\t\t<title>", $this->image['title'], "</title>\n";
					echo "\t\t<url>", (substr($this->image['scr'], 0, 7) == 'http://' ? Http::$serverUri . $this->base : '' ) . $this->image['src'], "</url>\n";
					echo "\t\t<link>", $this->controller->url($this->image['link'], null, true), "</link>\n";
				echo "\t</image>\n";
			}

			echo $content;
		echo "</channel>\n";
		echo "</rss>\n";
	}


	/**
	 * Returns formatted date in RFC822 format
	 * @param   int       timestamp
	 * @return  string
	 */
	public function date($time = null)
	{
		return gmdate(DATE_RFC2822, is_null($time) ? time() : $time);
	}


}


class RssItem extends Object
{


	/** @var string RssItem */
	public $title;

	/** @var string RssItem */
	public $link;

	/** @var string RssItem */
	public $description;

	/** @var string RssItem */
	public $author;

	/** @var string RssItem */
	public $category;

	/** @var string RssItem */
	public $comments;

	/** @var string RssItem */
	public $guid;

	/** @var string RssItem */
	public $date;

	/** @var View */
	protected $view;

	/** @var Controller */
	protected $controller;


	/**
	 * Contrustor
	 * @param   RssFeedView
	 * @return  void
	 */
	public function __construct(& $view)
	{
		$this->view = $view;
		$this->controller = Controller::get();
	}


	/**
	 * Renders rss item
	 * @return  string
	 */
	public function render()
	{
		$render = "\t<item>\n";

		if (!empty($this->title))
			$render .= "\t\t<title>" . htmlspecialchars(strip_tags($this->title)) . "</title>\n";

		if (!empty($this->link))
			$render .= "\t\t<link>" . $this->controller->url($this->link, null, true) . "</link>\n";

		if (!empty($this->guid))
			$render .= "\t\t<guid>" . $this->controller->url($this->guid, null, true) . "</guid>\n";
		elseif (!empty($this->link))
			$render .= "\t\t<guid>" . $this->controller->url($this->link, null, true) . "</guid>\n";

		if (!empty($this->description))
			$render .= "\t\t<description><![CDATA[\n" . $this->description . "\n]]></description>\n";

		if (!empty($this->author))
			$render .= "\t\t<author>" . htmlspecialchars($this->author) . "</author>\n";

		if (!empty($this->category))
			$render .= "\t\t<category>" . htmlspecialchars($this->category) . "</category>\n";

		if (!empty($this->comments))
			$render .= "\t\t<comments>" . $this->controller->url($this->comments, null, true) . "</comments>\n";

		if (!empty($this->date))
			$render .= "\t\t<pubDate>" . $this->view->date(strtotime($this->date)) . "</pubDate>\n";

		$render .= "\t</item>\n";
		return $render;
	}


}