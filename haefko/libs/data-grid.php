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
require_once dirname(__FILE__) . '/template.php';


class DataGrid extends Object
{


	/** @var int - Instances counter */
	protected static $counter = '';

	/** @var array - Columns, which you want to show */
	public $columns;

	/** @var false|array - Column, which you want to order; false = nothing, empta array = all */
	public $orderable = array();

	/** @var string - DataGrid name */
	protected $name;

	/** @var DbPreparedQuery */
	protected $query;

	/** @var string - Url for actions of the crud controller instance */
	protected $link;

	/** @var int - Limit for pagination */
	protected $limit = 20;

	/** @var array - Columns labels */
	protected $labels = array();

	/** @var array - Order of columns (for sort) */
	protected $order = array();


	/**
	 * Constructor
	 * @param   string     data grid name
	 * @return  DataGrid
	 */
	public function __construct($name = '')
	{
		if (empty($name))
			$name = 'dg' . self::$counter++;
		
		$this->name = $name;
		$this->initOrder();
	}


	/**
	 * Renders datagrid
	 * @return  string
	 */
	public function render()
	{
		$this->getData();
		if (empty($this->columns))
			$this->columns = $this->query->getColumnNames();


		$template = new Template();
		$template->setFile(dirname(__FILE__) . '/data-grid.template.phtml');
		$template->getHelper('html');

		# vars
		$template->grid = $this;
		$template->columns = $this->columns;
		$template->orderable = $this->orderable;
		$template->rows = $this->query->fetchAll();

		return $template->render();
	}


	/**
	 * Sets limit on the page
	 * @param   int  limit
	 * @return  DataGrid
	 */
	public function setLimit($limit)
	{
		$this->limit = max((int) $limit, 1);
		return $this;
	}


	/**
	 * Sets link mask for urls
	 * @param   string   link mask
	 * @return  DataGrid
	 */
	public function setLink($link)
	{
		$this->link = trim($link, '/');
		return $this;
	}


	/**
	 * Sets columns' labels
	 * @param   array    labels
	 * @return  DataGrid
	 */
	public function setLabels($labels)
	{
		$this->labels = $labels;
		return $this;
	}


	/**
	 * Sets datasource
	 * @param   DbPreparedResult   datasource
	 * @return  DataGrid
	 */
	public function setQuery(DbPreparedResult $query)
	{
		$this->query = $query;
		return $this;
	}


	/**
	 * Returns query
	 * @return  DbPreparedResult 
	 */
	public function getQuery()
	{
		return $this->query;
	}


	/**
	 * Creates link
	 * @param   string  action
	 * @param   mixed   param
	 * @return  string
	 */
	public function url($action, $param = null)
	{
		if ($param === null)
			return Controller::get()->url($this->link, null, array(
				'action' => $action
			));
		else
			return Controller::get()->url($this->link . '/' . $param, null, array(
				'action' => $action
			));
	}

	
	public function columnUrl($column)
	{
		return Controller::get()->url('', array(
			$this->name . '-order' => $this->getOrderState($column))
		);
	}


	/**
	 * Returns column order state class
	 * @param   string  column name
	 * @return  string
	 */
	public function columnStateClass($column)
	{
		if (!isset($this->order[$column]))
			return array();

		return array(
			'class' => $this->order[$column]['state'] === 'a' ? 'asc' : 'desc'
		);
	}


	/**
	 * Returns column order num
	 * @param   string  column name
	 * @return  string
	 */
	public function columnStateNum($column)
	{
		if (!isset($this->order[$column]))
			return '';

		return '<span class="order">' . ($this->order[$column]['order'] + 1) . '</span>';
	}


	/**
	 * Returns column label
	 * @param   string  column name
	 * @return  string
	 */
	public function columnLabel($column)
	{
		if (isset($this->labels[$column]))
			return $this->labels[$column];
		else
			return ucfirst($column);
	}


	/**
	 * Loads data for datagrid
	 * @return  void
	 */
	protected function getData()
	{
		$page = $this->getVar('page');
		$order = $this->getSqlOrder();

		$this->query->setOrder($order);
		$this->query->setPagination($page, $this->limit);
		$this->query->paginator->variableName = $this->name . '-page';
		$this->query->execute();
	}

	
	/**
	 * Transforms url table order as array
	 * @return  array
	 */
	private function initOrder()
	{
		$order = $this->getVar('order');
		if (empty($order) || $this->orderable === false)
			return;

		$order = explode('|', $order);
		$res = array();
		foreach ($order as $i => $val)
			$name = substr($val, 1);
			if (!empty($this->orderable) && !in_array($name, $this->orderable))
				continue;

			$this->order[$name] = array(
				'order' => $i,
				'state' => $val[0]
			);
	}


	/**
	 * Returns variable content
	 * @param   string   variable name
	 * @return  string
	 */
	protected function getVar($name)
	{
		$name = $this->name . '-' . $name;
		return Application::get()->router->get($name);
	}


	/**
	 * Returns sql order
	 * @return  string
	 */
	protected function getSqlOrder()
	{
		$sql = array();
		foreach ($this->order as $column => $val) {
			$sql[] = $column . ($val['state'] == 'd' ? ' DESC' : ' ASC');
		}	
		
		return implode(', ', $sql);
	}


	/**
	 * Gets url expresison for table order by $column
	 * @param   string  column
	 * @return  string
	 */
	protected function getOrderState($column)
	{
		$order = $this->order;

		if (!isset($order[$column]['state']))
			$order[$column]['state'] = 'a';
		elseif ($order[$column]['state'] == 'a')
			$order[$column]['state'] = 'd';
		else
			unset($order[$column]);

		$res = array();
		foreach ($order as $key => $val)
			$res[] = $val['state'] . $key;

		if(empty($res))
			return null;
		else
			return implode('|', $res);
	}


}