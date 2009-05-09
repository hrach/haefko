<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Database
 */


require_once dirname(__FILE__) . '/result.php';


class DbPreparedResult extends DbResult
{


	/** @var Paginator */
	public $paginator;


	/**
	 * Sets sql order
	 * @param mixed $order
	 * @return  DbResult  $this
	 */
	public function setOrder($order)
	{	
		if (!empty($order))
			$this->query .= ' ORDER BY ' . $order;

		return $this;
	}


	/**
	 * Sets pagination
	 * Limit defautl is 10
	 * Sometimes sql query needs manualy counts total rows num
	 * @param   int       page
	 * @param   int       limit
	 * @param   int       count pages
	 * @return  DbResult  $this
	 */
	public function setPagination($page, $limit = 10, $count = null)
	{
		if ($this->executed)
			throw new Exception("You can't paginate excecuted query.");

		if (empty($count))
			$count = db::fetchField(preg_replace('#select (.+) from#si', 'SELECT COUNT(*) FROM', $this->query));
		if ($page < 1)
			$page = 1;

		$this->query .= ' LIMIT ' . ($page - 1) * $limit . ', ' . $limit;

		require_once dirname(__FILE__) . '/../paginator.php';
		$this->paginator = new Paginator($page, ceil($count / $limit));

		return $this;
	}


	/**
	 * Sets association
	 * @param   string    main table
	 * @param   array     other tables in relation hasMany
	 * @return  DbResult  $this
	 */
	public function associate($main, $hasMany = array())
	{
		$this->association[0] = $main;
		$this->association[1] = (array) $hasMany;

		return $this;
	}


}