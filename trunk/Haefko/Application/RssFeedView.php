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



require_once dirname(__FILE__) . '/View.php';



/**
 * Trida RssView obstarava nacitani view a jeho vypln rss obsahem
 */
class RssFeedView extends View implements IView
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

	/** @var array Rss item */
	protected $ext = 'rss.php';

	/** @var int Rss verze */
	protected $version = 2;

	/** @var array Rss item */
	protected $items = array();



	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();

		if (Config::read('Core.debug') > 1)
			Config::write('Core.debug', 1);

		$this->version = Config::read('RssFeedView.version', $this->version);
	}



	/**
	 * Vrati instanci - polozku feedu
	 * @return  RssViewItem
	 */
	public function item()
	{
		$this->items[] = $item = new RssFeedViewItem($this);
		return $item;
	}



	/**
	 * Render sablony
	 * @return  string
	 */
	public function render()
	{
		parent::render();

		if (!$this->controller->app->error)
			Http::mimeType('application/rss+xml');

		$this->createFeed();

		return ob_get_clean();
	}



	/**
	 * Vygeneruje xml rss kanalu
	 * @return  void
	 */
	public function createFeed()
	{
		if ($this->version == 1) {
			echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			echo "<rdf:RDF \n xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" \n xmlns=\"http://purl.org/rss/1.0/\">\n"; 
			echo "<channel>\n";
				echo "\t<title>", htmlspecialchars($this->title), "</title>\n";
				echo "\t<link>", $this->controller->url($this->link, true), "</link>\n";
				echo "\t<description>", htmlspecialchars($this->description), "</description>\n";

				if (isset($this->image['src']))
					echo "\t<image rdf:resource=\"", (substr($this->image['scr'], 0, 7) == 'http://' ? Http::$serverUri . $this->base : '' ) . $this->image['src'], "\" />\n";

				echo "\t<items>\n\t\t<rdf:Seq>\n";
				foreach ($this->items as $item)
					echo "\t\t<rdf:li resource=\"", $this->controller->url($item->link, true), "\" />\n";
				echo "\t\t</rdf:Seq>\n\t</items>\n";
			echo "</channel>\n";

			foreach ($this->items as $item)
				$item->render();

			echo "</rdf:RDF>\n";
		} else {
			echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			echo "<rss version=\"2.0\">\n"; 
			echo "<channel>\n";
				echo "\t<title>", htmlspecialchars($this->title), "</title>\n";
				echo "\t<description>", htmlspecialchars($this->description), "</description>\n";
				echo "\t<link>", $this->controller->url($this->link, true), "</link>\n";
				echo "\t<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
				echo "\t<lastBuildDate>", $this->date(), "</lastBuildDate>\n";
				echo "\t<generator>Haefko - your php5 framework</generator>\n";

				if (isset($this->image['title']) && isset($this->image['src']) && isset($this->image['link'])) {
					echo "\t<image>\n";
						echo "\t\t<title>", $this->image['title'], "</title>\n";
						echo "\t\t<url>", (substr($this->image['scr'], 0, 7) == 'http://' ? Http::$serverUri . $this->base : '' ) . $this->image['src'], "</url>\n";
						echo "\t\t<link>", $this->controller->url($this->image['link'], true), "</link>\n";
					echo "\t</image>\n";
				}

				foreach ($this->items as $item)
					$item->render();
			echo "</channel>\n";
			echo "</rss>\n";
		}
	}



	/**
	 * Vrati datum ve formatu RFC822 pro RSS
	 * @param   int     timestamp
	 * @return  string
	 */
	public function date($time = null)
	{
		return gmdate(DATE_RFC2822, is_null($time) ? time() : $time);
	}



}



/**
 * Pomocna trida pro tvrobu polozek v RssFeedu
 */
class RssFeedViewItem
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

	/** @var string RssItem */
	public $source;

	/** @var RssFeedView */
	protected $view;



	/**
	 * Konstruktor
	 * @param   RssFeedView
	 * @return  void
	 */
	public function __construct(& $view)
	{
		$this->view = $view;
	}



	/**
	 * Funkce vrati xml kod polozky
	 * @return  string
	 */
	public function render()
	{
		echo "\t<item>\n";

		if ($this->view->version == 1) {
			if (!empty($this->title))
				echo "\t\t<title>", htmlspecialchars(strip_tags($this->title)), "</title>\n";

			if (!empty($this->link))
				echo "\t\t<link>", $this->view->controller->url($this->link, true), "</link>\n";

			if (!empty($this->description))
				echo "\t\t<description><![CDATA[", $this->description, "]]></description>\n";
		} else {
			if (!empty($this->title))
				echo "\t\t<title>", htmlspecialchars(strip_tags($this->title)), "</title>\n";

			if (!empty($this->link))
				echo "\t\t<link>", $this->view->controller->url($this->link, true), "</link>\n";

			if (!empty($this->guid))
				echo "\t\t<guid>", $this->view->controller->url($this->guid, true), "</guid>\n";
			elseif (!empty($this->link))
				echo "\t\t<guid>", $this->view->controller->url($this->link, true), "</guid>\n";

			if (!empty($this->description))
				echo "\t\t<description><![CDATA[", $this->description, "]]></description>\n";

			if (!empty($this->author))
				echo "\t\t<author>", htmlspecialchars($this->author), "</author>\n";

			if (!empty($this->category))
				echo "\t\t<category>", htmlspecialchars($this->category), "</category>\n";

			if (!empty($this->comments))
				echo "\t\t<comments>", $this->view->controller->url($this->comments, true), "</comments>\n";

			if (!empty($this->date))
				echo "\t\t<pubDate>", $this->view->controller->view->date(strtotime($this->date)), "</pubDate>\n";

			if (!empty($this->source))
				echo "\t\t<source>", $this->source, "</source>\n";
		}

		echo "\t</item>\n";
	}



}