<?php


class GroupsController extends Controller
{


    public function indexAction()
    {
        $this->view->groups = $this->model->groups();
    }


}