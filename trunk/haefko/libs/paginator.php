<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko
 */


class Paginator extends Object
{


	/** @var int */
	protected $page;

	/** @var int */
	protected $pages;

	/** @var bool */
	protected $prev;

	/** @var bool */
	protected $next;


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
		$this->prev = $this->page > 1;
		$this->next = $this->page < $this->pages;
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
	public function getPrev()
	{
		return $this->prev;
	}


	/**
	 * Returns true when exists next page
	 * @return  int
	 */
	public function getNext()
	{
		return $this->next;
	}


}