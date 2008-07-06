<?php


class MessagesController extends Controller
{


    public function groupAction($group)
    {
        if ($this->getArg('group') === false) {
            $this->app->error('404');
        }

        $this->view->messages = $this->model->messages($group);
    }


    public function editAction($message)
    {
        $form = $this->form();

        if ($form->isSubmit()) {
            if ($form->isValid()) {

                $data = $form->getData();
                dibi::query('UPDATE [demo_messages] SET', $data, 'WHERE [message_id] = %i', $message);
                $this->redirect("/messages/$data[group_id]");
            }
        } else {
            $form->setDefaults($this->model->message($message));
        }

        $this->view->form = $form;
    }


    public function createAction()
    {
        $form = $this->form();

        if ($form->isSubmit() && $form->isValid()) {
            $data = $form->getData();
            dibi::query('INSERT INTO [demo_messages]', $data);
            $this->redirect("/messages/$data[group_id]");
        }

        $form->setDefaults(array('date' => date('Y-m-d H:i')));
        $this->view->form = $form;
    }


    private function form()
    {
        $groups = dibi::query('SELECT * FROM [demo_groups] ORDER BY [name]')->fetchPairs();

        $form = new Form('{url}');
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