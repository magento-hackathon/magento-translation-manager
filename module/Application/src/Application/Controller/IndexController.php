<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    protected $_translationBaseTable = null;
    protected $_translationTable = null;

    /**
     * @var array - supported Locales
     */
    protected $_supportedLocale = null;


    public function indexAction()
    {


        var_dump($this->getSupportedLocales());
        return new ViewModel();
    }

    /**
     * @return array
     */
    protected function getSupportedLocales()
    {
        if (null == $this->_supportedLocale) {
            $sm = $this->getServiceLocator();
            /* @var $resourceModel \Application\Resource\SupportedLocale */
            $resourceModel = $sm->get('Application\Resource\SupportedLocale');
            $this->_supportedLocale = $resourceModel->fetchAll();
        }
        return $this->_supportedLocale;
    }



}
