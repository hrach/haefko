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

	/** @var string - Name of control with referer url */
	public static $CRUD_REFERER = 'crud_referer';

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
	protected $labels = array(
		'submit' => 'Save',
	);

	/** @var string - Referer */
	protected $referer;


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
		$table = $this->getTable();
		$form = $this->getForm($table);
		if ($form->isSubmit()) {
			$data = $form->data;
			unset($form->data[self::$CRUD_REFERER]);
			$table->import($this->processData($form->data, false))
			      ->save();

			$this->refererRedirect($data);
		}
	}


	/**
	 * Update action
	 * @param mixed $entry primary key value
	 */
	public function updateAction($entry)
	{
		$table = $this->getTable($entry);
		$form = $this->getForm($table);

		$row = $table->get();
		if (empty($entry) || empty($row))
			$this->error();


		$form->setDefaults($row);
		if ($form->isSubmit()) {
			$data = $form->data;
			unset($form->data[self::$CRUD_REFERER]);
			$table->import($this->processData($form->data, true))
			      ->save();

			$this->refererRedirect($data);
		}
	}


	/**
	 * Delete action
	 */
	public function deleteAction($entry)
	{
		$form = $this->getDeleteForm($entry);
		if ($form->isSubmit()) {
			$data = $form->data;
			unset($form->data[self::$CRUD_REFERER]);
			if ($form->isSubmit('yes'))
				$this->getTable($form->data['entry'])->remove();

			$this->refererRedirect($data);
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

		return $this->url($link, array('action' => $action));
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
	protected function getForm(DbTable $table)
	{
		$form = $this->template->crudEditForm = $table->getForm($this->editColumns,
			$this->labels, null, 'crudEditForm');

		# referer
		if (Http::$request->getReferer() == Http::$request->getFullRequest())
			$referer = $this->crudUrl('index');
		else
			$referer = Http::$request->getReferer();
		$form->addHidden(self::$CRUD_REFERER)->setDefaults(array(
			self::$CRUD_REFERER => $referer,
		));

		return $form;
	}


	/**
	 * Returns delete form
	 * @param mixed $entry
	 */
	protected function getDeleteForm($entry)
	{
		$form = $this->template->crudDeleteForm = new Form(null, 'crudDeleteForm');
		$form->addHidden('entry')
		     ->addProtection()
		     ->addSubmit('yes', 'Yes, remove')
		     ->addSubmit('no', 'No')
		     ->setRenderer('div');

		# referer
		if (Http::$request->getReferer() == Http::$request->getFullRequest())
			$referer = $this->crudUrl('index');
		else
			$referer = Http::$request->getReferer();

		$form->addHidden(self::$CRUD_REFERER)->setDefaults(array(
			self::$CRUD_REFERER => $referer,
			'entry' => $entry,
		));

		return $form;
	}


	/**
	 * Returns datagrid component
	 * @return DataGrid
	 */
	protected function getDataGrid()
	{
		$grid = new DataGrid(null, $this->application->cache);
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
	 * Redirects by referer
	 * @param array $refererData
	 */
	protected function refererRedirect($refererData)
	{
		if (!empty($refererData[self::$CRUD_REFERER])) {
			Http::$response->redirect($refererData[self::$CRUD_REFERER]);
			exit;
		} else {
			Http::$response->redirect(Http::$serverURL . $this->crudUrl('index'));
			exit;
		}
	}


}