<?php

namespace Application\Controller;

use Application\Model\Translation;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{
    public function indexAction()
    {
        // prepare view
        $view =  new ViewModel(array(

        ));

        return $view;
    }
}