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
	 * @param   int   page
	 * @param   int   total records
	 * @param   int   records on page
	 * @return  Paginator
	 */
	public function __construct($page, $total, $limit)
	{
		$pages = ceil($total / $limit);
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
		return !$this->hasPrev;
	}


	/**
	 * Returns true when current page is the last
	 * @return  bool
	 */
	public function isLast()
	{
		return !$this->hasNext;
	}


}