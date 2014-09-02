<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Model\Translation;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    const DEFAULT_LOCALE = 'de_DE';

    const MESSAGE_INFO = 'info';
    const MESSAGE_WARN = 'warn';
    const MESSAGE_ERROR = 'error';
    const MESSAGE_SUCCESS = 'success';

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

    /**
     * @var array - system messages
     */
    protected $_messages = array( /* type => array (message) */ );


    public function indexAction()  // Translation grid
    {
        // init grid
        $currentLocale = self::DEFAULT_LOCALE;
        $currentFile = null;
        if ($this->params()->fromQuery('locale')) {
            $currentLocale = $this->params()->fromQuery('locale');
        }
        if ($this->params()->fromQuery('file')) {
            $currentFile = (array)$this->params()->fromQuery('file');
        }

        // save form
        if ($this->params()->fromPost('rowid')) {
            $translationLocale = $this->params()->fromPost('translation_locale');
            // split POST params into rows
            $formRows = array( /* rowid => array(field => value) */ );
            $postParams = $this->params()->fromPost();
            foreach ($postParams as $postKey => $postValue) {
                if (preg_match('@(row\d+)_(.+)@', $postKey, $matches)) {
                    $formRows[$matches[1]][$matches[2]] = $postValue;
                }
            }

            // decide if one or all elements should be saved
            if ('all' == $this->params()->fromPost('rowid')) {
                $noError = true;
                foreach ($formRows as $row) {
                    $row['locale'] = $translationLocale;
                    $success = $this->saveTranslationElement($row);
                    $noError &= (bool)$success;
                }

                if (false == $noError) {
                    $this->addMessage('Error saving one or more elements', self::MESSAGE_ERROR);
                } else {
                    $this->addMessage('All elements are saved successfully', self::MESSAGE_SUCCESS);
                }
            } else {
                $rowId = $this->params()->fromPost('rowid');
                $formRows[$rowId]['locale'] = $translationLocale;
                $success = $this->saveTranslationElement($formRows[$rowId]);

                if (false == $success) {
                    $this->addMessage('Error saving element', self::MESSAGE_ERROR);
                } else {
                    $this->addMessage(sprintf('Element %s saved successfully', $success), self::MESSAGE_SUCCESS);
                }
            }
        }

        // prepare view
        $view =  new ViewModel(array(
            'supportedLocales' => $this->getSupportedLocales(),
            'translations'     => $this->getResourceTranslation()->fetchByLanguageAndFile($currentLocale, $currentFile),
            'translationBase'  => $this->getResourceTranslationBase()->fetchAll(),
            'translationFiles' => $this->getResourceTranslationBase()->getTranslationFileNames(),
            'currentLocale'    => $currentLocale,
            'currentFile'      => (array)$currentFile,
            'messages'         => $this->_messages,
        ));

        return $view;
    }

    /**
     * save Translation element with given data
     *
     * @param array $element - Translation element data
     * @return int|false - id of saved element
     */
    protected function saveTranslationElement($element)
    {
        $translation = null;
        if (isset($element['id'])) {
            $translation = $this->getResourceTranslation()->getTranslation($element['id']);
            if (false == $translation) {
                $translation = new Translation();
            }
            $translation->setOptions($element);
        } else {
            $translation = new Translation($element);
        }

        $success = $this->getResourceTranslation()->saveTranslation($translation);

        return $success;
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
}
