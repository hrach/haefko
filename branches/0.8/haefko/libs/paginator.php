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


class Paginator extends Object
{


	/** @var int */
	protected $page;

	/** @var int */
	protected $pages;

	/** @var bool */
	protected $hasPrev;

	/** @var bool */
	protected $hasNext;


	/**
	 * Constructor
	 * @param   int  page
	 * @param   int  pages
	 * @return  void
	 */
	public function __construct($page, $pages)
	{
		$page = (int) $page;
		$this->page = $page < 1 ? 1 : $page;
		$this->pages = (int) $pages;
		$this->hasPrev = $this->page > 1;
		$this->hasNext = $this->page < $this->pages;
	}


	/**
	 * Returns current page num
	 * @return  int
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * Returns sum of pages
	 * @return  int
	 */
	public function getPages()
	{
		return $this->pages;
	}


	/**
	 * Returns true when exists prev page
	 * @return  bool
	 */
	public function hasPrev()
	{
		return $this->hasPrev;
	}


	/**
	 * Returns true when exists next page
	 * @return  bool
	 */
	public function hasNext()
	{
		return $this->hasNext;
	}


	/**
	 * Returns true when current page is the first
	 * @return  bool
	 */
	public function isFirst()
	{
		return $this->page == 1;
	}


	/**
	 * Returns true when current page is the last
	 * @return  bool
	 */
	public function isLast()
	{
		return $this->page == $this->pages;
	}


	/**
	 * Renders pagination bar
	 * @param   string    mask of link with page
	 * @param   array     options: surround => count of surrounding links, prev => label text, next => label text
	 * @return  string
	 */
	public function render($mask, $options = array())
	{
		$options = array_merge(array(
			'surround' => 2,
			'prev' => '&laquo; Previous',
			'next' => 'Next &raquo;'
		), $options);

		$surround = $options['surround'];
		$render = "<div class=\"pagination\"><ul>\n";


		# render previous
		if ($this->hasPrev) {
			$prev = $this->link($mask, $this->page - 1, $options['prev']);
			$render .= "<li class=\"prev\">$prev</li>\n";
		} else {
			$render .= "<li class=\"prev-off\">$options[prev]</li>\n";
		}


		# not enough pages to bother breaking it up
		if ($surround === false || $this->pages < 7 + ($surround * 2)) {
			for ($counter = 1; $counter <= $this->pages; $counter++)
				$render .= $this->listLink($mask, $counter, $this->page == $counter);

		# enough pages to hide some
		} else {

			# close to beginning; only hide later pages
			if ($this->page < ($surround * 2) + 4 && $this->page < $surround + 4) {
				for ($counter = 1; $counter <= max(2 + $surround, $this->page + $surround); $counter++)
					$render .= $this->listLink($mask, $counter, $this->page == $counter);

				$render .= "<li class=\"hellip\">&hellip;</li>\n";
				$render .= $this->listLink($mask, $this->pages - 1);
				$render .= $this->listLink($mask, $this->pages);

			# close to end; only hide early pages
			} elseif ($this->page >= $this->pages - $surround - 2) {
				$render .= $this->listLink($mask, 1);
				$render .= $this->listLink($mask, 2);
				$render .= "<li class=\"hellip\">&hellip;</li>\n";

				for ($counter = min($this->pages - 2 * $surround + 1, $this->page - $surround); $counter <= $this->pages; $counter++)
					$render .= $this->listLink($mask, $counter, $this->page == $counter);

			# in middle; hide some front and some back
			} else {
				$render .= $this->listLink($mask, 1);
				$render .= $this->listLink($mask, 2);
				$render .= "<li class=\"hellip\">&hellip;</li>\n";

				for ($counter = $this->page - $surround; $counter <= $this->page + $surround; $counter++)
					$render .= $this->listLink($mask, $counter, $this->page == $counter);

				$render .= "<li class=\"hellip\">&hellip;</li>\n";
				$render .= $this->listLink($mask, $this->pages - 1);
				$render .= $this->listLink($mask, $this->pages);
			}
		}

		# render next
		if ($this->hasNext) {
			$next = $this->link($mask, $this->page + 1, $options['next']);
			$render .= "<li class=\"next\">$next</li>\n";
		} else {
			$render .= "<li class=\"next-off\">$options[next]</li>\n";
		}

		$render .= "</ul></div>\n";
		return $render;
	}


	/**
	 * Renders link for list html element
	 * @param   string   mask
	 * @param   int      page
	 * @param   bool     is page current?
	 * @return  string
	 */
	private function listLink($mask, $i, $current = false)
	{
		$link = $this->link($mask, $i);

		if ($current)
			return "<li class=\"active\">$link</li>\n";
		else
			return "<li>$link</li>\n";
	}


	/**
	 * Renders link element
	 * @param   string    mask
	 * @param   int       page
	 * @param   int       text
	 * @return  string
	 */
	private function link($mask, $i, $text = null)
	{
		if (empty($text))
			$text = $i;

		if (class_exists('Application', false))
			$link = Controller::get()->url($mask, array('page' => $i));
		else
			$link = preg_replace('#<:page>#', $i, $mask);

		return "<a href=\"$link\">$text</a>";
	}


}