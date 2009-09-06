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

	/** @var string - Column name from which will be showed title of entry */
	protected $readableColumn;

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

	/** @var DbTable */
	protected $dbTable;


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
		$this->initTable();
		$form = $this->getForm();

		if ($form->isSubmit()) {
			$data = $this->processData($form->data, true);
			unset($data[self::$CRUD_REFERER]);
			$this->saveData($data);
			$this->refererRedirect($form->data);
		}
	}


	/**
	 * Update action
	 * @param mixed $entry primary key value
	 */
	public function updateAction($entry)
	{
		$this->initTable($entry);
		$form = $this->getForm();
		        $this->fillFormWithDefaults($form);

		if ($form->isSubmit()) {
			$data = $this->processData($form->data);
			unset($data[self::$CRUD_REFERER]);
			$this->saveData($data);
			$this->refererRedirect($form->data);
		}
	}


	/**
	 * Delete action
	 * @param mixed $entry primary key value
	 */
	public function deleteAction($entry)
	{
		$this->template->entry = $entry;
		$form = $this->getDeleteForm($entry);

		if ($form->isSubmit()) {
			if ($form->isSubmit('yes')) {
				$this->initTable($form->data['entry']);
				$this->deleteData();
			}

			$this->refererRedirect($data);
		} else {
			$this->initTable($entry);
			$this->findReadableColumn();
		}
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
	 * @param bool $isInsert
	 * @return array
	 */
	protected function processData($data, $isInsert = false)
	{
		return $data;
	}


	/**
	 * Returns table form
	 * @return Form
	 */
	protected function getForm()
	{
		$form = $this->template->crudEditForm = 
			$this->dbTable->getForm($this->editColumns, $this->labels, null, 'crudEditForm');

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
	 * @return Form
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
	 * Inits DbTable
	 */
	protected function initTable($pk = null)
	{
		$class = DbTable::init($this->table);
		if (empty($pk))
			$this->dbTable = new $class;
		else
			$this->dbTable = new $class($pk);
	}


	/**
	 * Saves data to db
	 * @param array $data
	 */
	protected function saveData($data)
	{
		return $this->dbTable->import($data)->save();
	}


	/**
	 * Deletes data form db
	 */
	protected function deleteData()
	{
		return $this->dbTable->remove();
	}


	/**
	 * Sets defaults for edit form
	 * @param Form $form
	 * @return Form
	 */
	protected function fillFormWithDefaults($form)
	{
		$row = $this->dbTable->get();
		if (empty($row))
			$this->error();

		return $form->setDefaults($row);
	}


	/**
	 * Finds entry representating value in readableColumn
	 * @throws Exception
	 */
	protected function findReadableColumn()
	{
		if (empty($this->readableColumn))
			return;

		$row = $this->dbTable->get($this->readableColumn);
		if (!empty($row))
			$this->template->entry = $row[$this->readableColumn];
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