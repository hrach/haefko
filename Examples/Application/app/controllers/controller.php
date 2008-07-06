<?php


class Controller extends CustomController
{


    public $helpers = array('js');


    public function init()
    {
        $this->view->title = 'Systém pro kontrolu zpráv';
    }


}