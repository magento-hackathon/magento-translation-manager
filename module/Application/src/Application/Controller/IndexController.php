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
    const DEFAULT_LOCALE = 'de_DE';

    /**
     * @var $_translationBaseTable \Application\Resource\TranslationBase
     */
    protected $_translationBaseTable = null;
    /**
     * @var $_translationBaseTable \Application\Resource\Translation
     */
    protected $_translationTable = null;

    /**
     * @var array - supported Locales
     */
    protected $_supportedLocale = null;


    public function indexAction()
    {
        // Translation grid

        $currentLocale = self::DEFAULT_LOCALE;
        $currentFile = null;
        if ($this->params()->fromQuery('locale')) {
            $currentLocale = $this->params()->fromQuery('locale');
        }
        if ($this->params()->fromQuery('file')) {
            $currentFile = (array)$this->params()->fromQuery('file');
        }

        return new ViewModel(array(
            'supportedLocales' => $this->getSupportedLocales(),
            'translations'     => $this->getResourceTranslation()->fetchByLanguageAndFile($currentLocale, $currentFile),
            'translationBase'  => $this->getResourceTranslationBase()->fetchAll(),
            'translationFiles' => $this->getResourceTranslationBase()->getTranslationFileNames(),
            'currentLocale'    => $currentLocale,
            'currentFile'      => (array)$currentFile,
        ));
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

    /**
     * get translation resource
     *
     * @return \Application\Resource\Translation
     */
    protected function getResourceTranslation()
    {
        if (null == $this->_translationTable) {
            $sm = $this->getServiceLocator();
            /* @var $resourceModel \Application\Resource\Translation */
            $resourceModel = $sm->get('Application\Resource\Translation');
            $this->_translationTable = $resourceModel;
        }
        return $this->_translationTable;
    }

    /**
     * get translation base resource
     *
     * @return \Application\Resource\TranslationBase
     */
    protected function getResourceTranslationBase()
    {
        if (null == $this->_translationBaseTable) {
            $sm = $this->getServiceLocator();
            /* @var $resourceModel \Application\Resource\TranslationBase */
            $resourceModel = $sm->get('Application\Resource\TranslationBase');
            $this->_translationBaseTable = $resourceModel;
        }
        return $this->_translationBaseTable;
    }

}
