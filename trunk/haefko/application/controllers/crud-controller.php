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

	/** @var bool */
	protected $allowTemplatePathReduction = true;


	/**
	 * Inits CRUD controller - creates instance of dbTable
	 * @throws Exception
	 */
	public function init()
	{
		$this->template->controllerTitle = Tools::camelize($this->table);
		$this->routing->controller = 'crud';
		parent::init();

		if (empty($this->link))
			throw new Exception('You have to defined link to the instace of CRUD controller by ' . $this->getClass() . '::$link.');
	}


	/**
	 * Index action - show paginate table contents
	 */
	public function indexAction()
	{
		$columns = implode(", ", (array) $this->columns);
		$query = $this->getQuery($columns);

		$grid = $this->template->grid = $this->getDataGrid();
		$grid->setQuery($query);

		if (!$grid->getData(true))
			$this->error();
	}


	/**
	 * Create action
	 */
	public function createAction()
	{
		$this->initReferer();
		$table = $this->getTable();
		$form = $this->template->form = $this->getForm($table);

		if ($form->isSubmit()) {
			$table->import($this->processData($form->data))
			      ->save();

			$this->refererRedirect($this->crudUrl('index'));
		}
	}


	/**
	 * Update action
	 * @param mixed $entry primary key value
	 */
	public function updateAction($entry)
	{
		$this->initReferer();
		$table = $this->getTable($entry);
		$form = $this->template->form = $this->getForm($table);
		
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

		$this->template->entry = $entry;
	}


	/**
	 * Returns crud url
	 * @param string $action action
	 * @param string $arg arg
	 * @return string
	 */
	public function crudUrl($action, $arg = null)
	{
		if (empty($arg))
			$link = $this->link;
		else
			$link = $this->link . '/' . $arg;

		return $this->url($link, null, array('action' => $action));
	}


	/**
	 * Processes saving form data
	 * @param array $data
	 * @return array
	 */
	protected function processData($data)
	{
		return $data;
	}


	/**
	 * Returns table form
	 * @param DbTable $table
	 * @return Form
	 */
	protected function getForm($table)
	{
		$form = $table->getForm($this->editColumns, $this->labels);
		return $form;
	}


	/**
	 * Returns datagrid component
	 * @return DataGrid
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
	 * @param mixed $pk primary key value
	 * @return DbTable
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
	 * @param string $columns
	 * @return DbPreparedResult
	 */
	protected function getQuery($columns)
	{
		if (empty($this->table))
			throw new Exception('You have to defined table name by ' . $this->getClass() . '::$table.');

		return Db::prepare("select $columns from %c", $this->table);
	}


	/**
	 * Inits referer
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
	 * @param string $default default url
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