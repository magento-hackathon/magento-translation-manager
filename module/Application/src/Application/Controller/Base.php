<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class Base extends AbstractActionController {
    const DEFAULT_LOCALE = 'de_DE';

    const MESSAGE_INFO = 'info';
    const MESSAGE_WARN = 'warning';
    const MESSAGE_ERROR = 'danger';
    const MESSAGE_SUCCESS = 'success';

    /**
     * @var array - system messages
     */
    protected $_messages = array( /* type => array (message) */ );

    /**
     * @var $_translationBaseTable \Application\Resource\TranslationBase
     */
    protected $_translationBaseTable = null;
    /**
     * @var $_translationTable \Application\Resource\Translation
     */
    protected $_translationTable = null;

    /**
     * @var array - supported Locales
     */
    protected $_supportedLocale = null;


    /**
     * add message to system message queue
     *
     * @param $message - message to note
     * @param string $level - message leven eg.g error or info
     */
    protected function addMessage($message, $level = self::MESSAGE_INFO)
    {
        $this->_messages[$level][] = $message;
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