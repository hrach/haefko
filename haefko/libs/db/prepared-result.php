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
 * @subpackage  Database
 */


require_once dirname(__FILE__) . '/result.php';


class DbPreparedResult extends DbResult
{

	/** @var Paginator */
	public $paginator;


	/**
	 * Sets sql order
	 * @param mixed $order
	 * @return DbResult
	 */
	public function setOrder($order)
	{	
		if (!empty($order))
			$this->query .= ' ORDER BY ' . $order;

		return $this;
	}


	/**
	 * Sets pagination
	 * @param int $pate page
	 * @param int $limit limit (default = 10)
	 * @param int $count count pages
	 * @return DbPreparedResult
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
		$this->paginator = new Paginator($page, $count, $limit);
		return $this;
	}


	/**
	 * Sets association
	 * @param string $main main table name
	 * @param string $hasMany table which is in relation hasMany
	 * @return DbPreparedResult
	 */
	public function setAssociation($main, $hasMany)
	{
		$this->association[0] = $main;
		$this->association[1] = (string) $hasMany;
		return $this;
	}


}