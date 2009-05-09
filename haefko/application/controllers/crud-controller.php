<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Application
 * @subpackage  Database
 */


class CrudController extends AppController
{

	
	/** @var string - Url for actions of the crud controller instance */
	protected $link;

	/** @var string - Table name */
	protected $table;

	/** @var int - Limit for pagination */
	protected $limit = 20;

	/** @var string|array */
	protected $cols = '*';

	/** @var Form */
	protected $form;

	/** @var DbTable */
	protected $dbTable;


	/**
	 * Inits CRUD controller - creates instance of dbTable
	 * @throws  Exception
	 * @return  void
	 */
	public function init()
	{
		$this->view->setRouting('controller', 'crud');
		parent::init();

		if (empty($this->table))
			throw new Exception('You have to defined table name by ' . $this->getClass() . '::$table.');

		if (empty($this->link))
			throw new Exception('You have to defined link to the instace of CRUD controller by ' . $this->getClass() . '::$link.');

		$this->dbTable = DbTable::initTable($this->table);
	}


	/**
	 * Index action - show paginate table contents
	 * @return  void
	 */
	public function indexAction()
	{
		$cols = implode(", ", (array) $this->cols);
		$query = db::prepare("select $cols from %c", $this->table);

		$grid = new DataGrid();
		$grid->setQuery($query)
		     ->setLimit($this->limit)
		     ->setLink($this->link)
		     ->getData();


		$this->view->grid = $grid;
	}


	public function createAction()
	{
		$this->initForm();
		
		
	}


	public function updateAction($id)
	{
		$this->initForm();
		$this->dbTable->setId($id);
		
		$row = $this->dbTable->get();
		if (empty($id) || empty($row))
			$this->error();

		$this->form->setDefaults($row);
		
		if ($this->form->isSubmit()) {
			$this->dbTable->import($this->form->data)
			              ->save();
		}
	}


	public function deleteAction()
	{

	}
	
	protected function initForm()
	{
		$this->form = $this->dbTable->getForm();
		$this->view->form = $this->form;
	}
	
	public function crudUrl($action, $param = null)
	{
		if ($param == null)
			return $this->url($this->link, null, array(
				'action' => $action
			));
		else
			return $this->url($this->link . '/' . $param, null, array(
				'action' => $action
			));
	}

}