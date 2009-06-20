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
 * @subpackage  Controller
 */


class CrudController extends AppController
{


	/** @var string - Table name */
	protected $table;
	
	/** @var string - Url for actions of the crud controller instance */
	protected $link;

	/** @var int - Limit for pagination */
	protected $limit = 20;

	/** @var string|array */
	protected $columns = '*';

	/** @var array - Columns which can be edited */
	protected $editColumns = array();

	/** @var array - Columns labels */
	protected $labels = array();

	/** @var string - Referer */
	protected $referer;


	/**
	 * Inits CRUD controller - creates instance of dbTable
	 * @throws  Exception
	 * @return  void
	 */
	public function init()
	{
		$this->view->controllerTitle = Tools::camelize($this->table);
		$this->view->setRouting('controller', 'crud');
		parent::init();

		if (empty($this->link))
			throw new Exception('You have to defined link to the instace of CRUD controller by ' . $this->getClass() . '::$link.');
	}


	/**
	 * Index action - show paginate table contents
	 * @return  void
	 */
	public function indexAction()
	{
		$columns = implode(", ", (array) $this->columns);
		$query = $this->getQuery($columns);

		$grid = $this->view->grid = $this->getDataGrid();
		$grid->setQuery($query);
	}


	/**
	 * Create action
	 * @return   void
	 */
	public function createAction()
	{
		$this->initReferer();
		$table = $this->getTable();
		$form = $this->view->form = $this->getForm($table);

		if ($form->isSubmit()) {
			$table->import($this->processData($form->data))
			      ->save();

			$this->refererRedirect($this->crudUrl('index'));
		}
	}


	/**
	 * Update action
	 * @param   mixed   primary key value
	 * @return  void
	 */
	public function updateAction($entry)
	{
		$this->initReferer();
		$table = $this->getTable($entry);
		$form = $this->view->form = $this->getForm($table);
		
		$row = $table->get();
		if (empty($entry) || empty($row))
			$this->error();


		$form->setDefaults($row);
		if ($form->isSubmit()) {
			$table->import($this->processData($form->data))
			      ->save();

			$this->refererRedirect($this->crudUrl('index'));
		}
	}


	/**
	 * Delete action
	 * @return   void
	 */
	public function deleteAction($entry)
	{
		$this->initReferer();
		if (!empty($_POST['yes'])) {
			$this->getTable($entry)->remove();
			$this->refererRedirect($this->crudUrl('index'));
		} elseif (!empty($_POST['no'])) {
			$this->refererRedirect($this->crudUrl('index'));
		}

		$this->view->entry = $entry;
	}


	/**
	 * Returns crud url
	 * @param   string    action
	 * @param   string    arg
	 * @return  string
	 */
	public function crudUrl($action, $arg = null)
	{
		if ($arg == null)
			return $this->url($this->link, null, array(
				'action' => $action
			));
		else
			return $this->url($this->link . '/' . $arg, null, array(
				'action' => $action
			));
	}


	/**
	 * Processes saving form data
	 * @param   array $data
	 * @return  array
	 */
	protected function processData($data)
	{
		return $data;
	}


	/**
	 * Returns table form
	 * @param   DbTable
	 * @return  Form
	 */
	protected function getForm($table)
	{
		$form = $table->getForm($this->editColumns, $this->labels);
		return $form;
	}


	/**
	 * Returns datagrid component
	 * @return  DataGrid
	 */
	protected function getDataGrid()
	{
		$grid = new DataGrid();
		return $grid->setLimit($this->limit)
		            ->setLink($this->link)
		            ->setLabels($this->labels);
	}


	/**
	 * Returns DbTable
	 * @param   mixed    primary key value
	 * @return  DbTable
	 */
	protected function getTable($pk = null)
	{
		$class = DbTable::init($this->table);
		if (empty($pk))
			return new $class();
		else
			return new $class($pk);
	}


	/**
	 * Returns prepared query - table data source
	 * @param   string  $columns
	 * @return  DbPreparedResult
	 */
	protected function getQuery($columns)
	{
		if (empty($this->table))
			throw new Exception('You have to defined table name by ' . $this->getClass() . '::$table.');

		return Db::prepare("select $columns from %c", $this->table);
	}


	/**
	 * Inits referer
	 * @return  void
	 */
	protected function initReferer()
	{
		$name = 'Crud.' . $this->getClass() . '.referer';
		if (Http::getReferer() == Http::getFullRequest())
			return;

		Session::write($name, Http::getReferer());
	}


	/**
	 * Redirects by referer or default url
	 * @param   string  default url
	 * @return  void
	 */
	protected function refererRedirect($default)
	{
		$name = 'Crud.' . $this->getClass() . '.referer';
		if (Session::exists($name)) {
			Http::headerRedirect(Session::read($name));
			Session::delete($name);
			exit;
		} else {
			$this->redirect($default);
		}
	}


}