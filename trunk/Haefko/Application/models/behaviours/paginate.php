<?php


class Paginate
{


	private $page;
	private $limit = 5;
	private $sql = '';


	public function config($page = 1)
	{
		$this->page = $page;
		Event::add('Db.beforeQuery', array($this, 'beforeQuery'));
		Event::add('Db.afterQuery', array($this, 'afterQuery'));
	}



	public function beforeQuery($query)
	{
		Event::remove('Db.beforeQuery', array($this, 'beforeQuery'));

		$this->sql = $query['sql'];
		$query['sql'] .= ' limit ' . ($this->page - 1) * $this->limit . ', ' . $this->limit;
	}



	public function afterQuery($query)
	{
		Event::remove('Db.afterQuery', array($this, 'afterQuery'));

		$count = db::fetchField(preg_replace('#select (.+) from#si', 'select count(*) from', $this->sql));

		$query['result']->page = $this->page;
		$query['result']->page_count = ceil($count / ($this->page * $this->limit));
		$query['result']->next = $this->page < $query['result']->page_count;
		$query['result']->prev = $this->page > 1;
	}



}