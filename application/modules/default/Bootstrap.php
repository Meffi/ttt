<?php
/**
 * Description of Bootstrap
 */
class Default_Bootstrap extends Zend_Application_Module_Bootstrap 
{
    public function _initREST()
    {

        Zend_Session::start();


    }
}
