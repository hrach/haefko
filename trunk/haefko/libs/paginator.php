<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko_Libs
 */


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
		$this->page = (int) $page;
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
	 * @return  int
	 */
	public function getHasPrev()
	{
		return $this->hasPrev;
	}


	/**
	 * Returns true when exists next page
	 * @return  int
	 */
	public function getHasNext()
	{
		return $this->hasNext;
	}


	/**
	 * Renders pagination bar
	 * @param   string    mask of link with page
	 * @param   array     options: round => num of pages around actual; prev => label; next => label
	 * @return  string
	 */
	public function render($mask, $options = array())
	{
		$options = array_merge(array(
			'round' => 3,
			'prev' => '&laquo; ' . __('Previous'),
			'next' => __('Next') . ' &raquo;'
		), $options);

		$round = $options['round'];
		$render = '<div class="pagination"><ul>';


		# render previous
		if ($this->hasPrev) {
			$prev = $this->link($mask, $this->page - 1);
			$render .= "<li class=\"previous\"><a href=\"$prev\">$options[prev]</a></li>";
		} else {
			$render .= "<li class=\"previous-off\">$options[prev]</li>";
		}


		# not enough pages to bother breaking it up
		if ($round === false || $this->pages < 7 + ($round * 2)) {
			for ($counter = 1; $counter <= $this->pages; $counter++)
				$render .= $this->listLink($mask, $counter, $this->page == $counter);

		# enough pages to hide some
		} else {

			# close to beginning; only hide later pages
			if ($this->page < ($round * 2) + 4 && $this->page < $round + 4) {
				for ($counter = 1; $counter < ($round * 2) + 4; $counter++)
					$render .= $this->listLink($mask, $counter, $this->page == $counter);

				$render .= '<li class="hellip">&hellip;</li>';
				$render .= $this->listLink($mask, $this->pages - 1);
				$render .= $this->listLink($mask, $this->pages);

			# close to end; only hide early pages
			} elseif ($this->page >= $this->pages - $round - 2) {
				$render .= $this->listLink($mask, 1);
				$render .= $this->listLink($mask, 2);
				$render .= '<li class="hellip">&hellip;</li>';

				for ($counter = $this->pages - ($round * 2) - 2; $counter <= $this->pages; $counter++)
					$render .= $this->listLink($mask, $counter, $this->page == $counter);

			# in middle; hide some front and some back
			} else {
				$render .= $this->listLink($mask, 1);
				$render .= $this->listLink($mask, 2);
				$render .= '<li class="hellip">&hellip;</li>';

				for ($counter = $this->page - $round; $counter <= $this->page + $round; $counter++)
					$render .= $this->listLink($mask, $counter, $this->page == $counter);

				$render .= '<li class="hellip">&hellip;</li>';
				$render .= $this->listLink($mask, $this->pages - 1);
				$render .= $this->listLink($mask, $this->pages);
			}
		}

		# render next
		if ($this->hasNext) {
			$next = $this->link($mask, $this->page + 1);
			$render .= "<li class=\"next\"><a href=\"$next\">$options[next]</a></li>";
		} else {
			$render .= "<li class=\"next-off\">$options[next]</li>";
		}

		$render .= "</ul></div>";
		return $render;
	}


	private function listLink($mask, $i, $current = false)
	{
		$link = $this->link($mask, $i);

		if ($current)
			return "<li class=\"active\">$link</li>";
		else
			return "<li>$link</li>";
	}


	/**
	 * Renders link element
	 * @param   string    mask
	 * @param   int       page
	 * @return  string
	 */
	private function link($mask, $i)
	{
		if (class_exists('Application', false))
			$link = Controller::get()->url($mask, array('page' => $i));
		else
			$link = preg_replace('#<:page>#', $i, $link);

		return "<a href=\"$link\">$i</a>";
	}


}