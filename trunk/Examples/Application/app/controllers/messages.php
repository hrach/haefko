<?php

class MessagesController extends CustomController
{



	function init()
	{
		$this->view->title = 'Systém pro kontrolu zpráv';
	}



	function groupsAction()
	{
		$this->view->groups = $this->getGroups();
	}



	function messagesAction($group)
	{
		if ($this->getArg('group') === false) {
			$this->app->error('404');
		}
	}



	public function editAction()
	{
		$message = $this->getArg('message');
		$form = $this->getForm('/edit/{args}');

		if ($form->isSubmit()) {
			if ($form->isValid()) {
				$data = $form->getData();
				dibi::query('UPDATE [demo_messages] SET', $data, 'WHERE [message_id] = %i', $message);
				$this->redirect("/messages/group:$data[group_id]");
			}
		} else {
			$form->setDefaults($this->getMessage($message)->fetch());
		}

		$this->view->form = $form;
	}



	public function createAction()
	{
		$form = $this->getForm('/create/{args}');

		if ($form->isSubmit() && $form->isValid()) {
			$data = $form->getData();
			dibi::query('INSERT INTO [demo_messages]', $data);
			$this->redirect("/messages/group:$data[group_id]");
		}

		$form->setDefaults(array('date' => date('Y-m-d H:i')));
		$this->view->form = $form;
	}



	function getGroups()
	{
		return dibi::query('
			SELECT *
			FROM [demo_groups]
		');
	}



	function getMessage($id)
	{
		return dibi::query('
			SELECT *
			FROM [demo_messages]
			WHERE [message_id] = %i', $id
		);
	}



	private function getForm($url)
	{
		$groups = dibi::query('SELECT * FROM [demo_groups] ORDER BY [name]')->fetchPairs();

		$form = new Form($url);
		$form->addSelect('group_id', $groups)
			 ->addText('author')
			 ->addText('text', true)
			 ->addText('date')
			 ->addSubmit();

		$form['author']->addRule(Form::FILLED, 'Vyplňte své jméno!');
		$form['text']->addRule(Form::FILLED, 'Zadejte text zprávy!');

		return $form;
	}



}