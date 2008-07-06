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



require_once dirname(__FILE__) . '/View.php';



/**
 * Trida RssView obstarava nacitani view a jeho vypln rss obsahem
 */
class RssView extends View implements IView
{



    public $title;
    public $link;
    public $description;
    public $lang;
    public $copyright;
    public $image = array();

    protected $ext = 'rss.php';

    private $items = array();



    /**
     * Konstruktor
     */
    public function __construct()
    {
        parent::__construct();

        if (Config::read('Core.debug', 0) == 2) {
            Config::write('Core.debug', 1);
        }
    }




    /**
     * Vrati instanci - polozku feedu
     * @return  RssViewItem
     */
    public function item()
    {
        $this->items[] = $item = new RssViewItem($this->controller);
        return $item;
    }



    /**
     * Render sablony
     * @return  string
     */
    public function render()
    {
        parent::render();

        if (!$this->controller->app->error) {
            Http::mimeType('application/rss+xml');
        }

        $this->createFeed();

        return ob_get_clean();
    }



    /**
     * Vygeneruje xml rss kanalu
     * @return  void
     */
    public function createFeed()
    {
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        echo "<rss version=\"2.0\">\n"; 
        echo "<channel>\n";

            echo "\t<title>", htmlspecialchars($this->title), "</title>\n";
            echo "\t<description>", htmlspecialchars($this->description), "</description>\n";
            echo "\t<link>", $this->controller->url($this->link, true), "</link>\n";
            echo "\t<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
            echo "\t<lastBuildDate>", $this->date(), "</lastBuildDate>\n";
            echo "\t<generator>Haefko - your php5 framework</generator>\n";

            if (isset($this->image['title']) && isset($this->image['url']) && isset($this->image['link'])) {
                echo "\t<image>\n";
                    echo "\t\t<title>", $this->image['title'], "</title>\n";
                    echo "\t\t<url>", $this->image['url'], "</url>\n";
                    echo "\t\t<link>", $this->image['link'], "</link>\n";
                echo "\t</image>\n";
            }

            foreach ($this->items as $item) {
                $item->render();
            }

        echo "</channel>\n";
        echo "</rss>\n";
    }



    /**
     * Vrati datum ve formatu RFC822 pro RSS
     * @param   int     timestamp
     * @return  string
     */
    public function date($time = false)
    {
        return gmdate(DATE_RFC822, !$time ? time() : $time);
    }



}



class RssViewItem
{



    public $title;
    public $link;
    public $description;
    public $author;
    public $category;
    public $comments;
    public $guid;
    public $date;
    public $source;

    private $controller;



    public function __construct($controller)
    {
        $this->controller = $controller;
    }



    public function render()
    {
        echo "\t<item>\n";
        if (!empty($this->title))
            echo "\t\t<title>", htmlspecialchars(strip_tags($this->title)), "</title>\n";

        if (!empty($this->link))
            echo "\t\t<link>", $this->controller->url($this->link, true), "</link>\n";

        if (!empty($this->guid))
            echo "\t\t<guid>", $this->controller->url($this->guid, true), "</guid>\n";
        elseif (!empty($this->link))
            echo "\t\t<guid>", $this->controller->url($this->link, true), "</guid>\n";

        if (!empty($this->description))
            echo "\t\t<description><![CDATA[", $this->description, "]]></description>\n";

        if (!empty($this->author))
            echo "\t\t<author>", htmlspecialchars($this->author), "</author>\n";

        if (!empty($this->category))
            echo "\t\t<category>", htmlspecialchars($this->category), "</category>\n";

        if (!empty($this->comments))
            echo "\t\t<comments>", $this->controller->url($this->comments, true), "</comments>\n";

        if (!empty($this->date))
            echo "\t\t<pubDate>", $this->controller->view->date(strtotime($this->date)), "</pubDate>\n";

        if (!empty($this->source))
            echo "\t\t<source>", $this->source, "</source>\n";

        echo "\t</item>\n";
    }



}