<?php

class Default_GameController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {

    }
    public function playAction()
    {
        $this->view->gameId = $this->getParam('id', 0);
    }



}

