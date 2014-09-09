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
    const MESSAGE_WARN = 'warning';
    const MESSAGE_ERROR = 'danger';
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

    /**
     * @var string - current locale choosed by user
     */
    protected $_currentLocale = self::DEFAULT_LOCALE;


    public function init()
    {
        if ($this->params()->fromQuery('locale')) {
            $this->_currentLocale = $this->params()->fromQuery('locale');
        }
    }

    public function indexAction()  // Translation grid
    {
        $this->init();

        // init grid
        $jumpToRow = null;
        $currentFile = null;
        $currentFilterUnclear = (bool)$this->params()->fromQuery('filter_unclear_translation');

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
                $errors = 0;
                $elementsModified = 0;
                foreach ($formRows as $row) {
                    try {
                        $row['locale'] = $translationLocale;
                        $modified = $this->saveTranslationElement($row);
                        if (false !== $modified) {
                            $elementsModified++;
                        }
                    } catch(\Exception $e) {
                        $errors++;
                    }
                }

                if (0 < $errors) {
                    $this->addMessage(sprintf('Error saving %d elements', $errors), self::MESSAGE_ERROR);
                }
                if (0 < $elementsModified) {
                    $this->addMessage(sprintf('%d elements modified successfully', $elementsModified), self::MESSAGE_SUCCESS);
                }
                if (0 == $elementsModified && 0 == $errors) {
                    $this->addMessage('No changes.', self::MESSAGE_INFO);
                }
            } else {
                $rowId = $this->params()->fromPost('rowid');
                $jumpToRow = $rowId;
                $formRows[$rowId]['locale'] = $translationLocale;
                try {
                    $success = $this->saveTranslationElement($formRows[$rowId]);

                    if (false == $success) {
                        $this->addMessage('No changes.', self::MESSAGE_INFO);
                    } else {
                        $this->addMessage(sprintf('Element saved successfully (element #%d)', $success), self::MESSAGE_SUCCESS);
                    }
                } catch(\Exception $e) {
                    $this->addMessage('Error saving element', self::MESSAGE_ERROR);
                }
            }
        }

        // prepare view
        $view =  new ViewModel(array(
            'supportedLocales'     => $this->getSupportedLocales(),
            'translations'         => $this->getResourceTranslation()->fetchByLanguageAndFile($this->_currentLocale, $currentFile, $currentFilterUnclear),
            'translationBase'      => $this->getResourceTranslationBase()->fetchAll(),
            'translationFiles'     => $this->getResourceTranslationBase()->getTranslationFileNames(),
            'currentLocale'        => $this->_currentLocale,
            'currentFile'          => (array)$currentFile,
            'currentFilterUnclear' => $currentFilterUnclear,
            'messages'             => $this->_messages,
            'jumpToRow'            => $jumpToRow,
        ));

        return $view;
    }

    public function editAction()  // Translation detail
    {
        $this->init();
        $baseId = $this->params('base_id');
        $baseTranslation = $this->getResourceTranslationBase()->getTranslationBase($baseId);

        // save data
        if ($this->params()->fromPost('rowid')) {
            // split POST params into rows
            $formRows = array( /* rowid => array(field => value) */ );
            $postParams = $this->params()->fromPost();
            foreach ($postParams as $postKey => $postValue) {
                if (preg_match('@(row.{5})_(.+)@', $postKey, $matches)) {
                    $formRows[$matches[1]][$matches[2]] = $postValue;
                }
            }


            // decide if one or all elements should be saved
            if ('all' == $this->params()->fromPost('rowid')) {
                $errors = 0;
                $elementsModified = 0;
                foreach ($formRows as $row) {
                    try {
                        $row['baseId'] = $baseTranslation->getBaseId();
                        $modified = $this->saveTranslationElement($row);
                        if (false !== $modified) {
                            $elementsModified++;
                        }
                    } catch(\Exception $e) {
                        $errors++;
                    }
                }

                if (0 < $errors) {
                    $this->addMessage(sprintf('Error saving %d elements', $errors), self::MESSAGE_ERROR);
                }
                if (0 < $elementsModified) {
                    $this->addMessage(sprintf('%d elements modified successfully', $elementsModified), self::MESSAGE_SUCCESS);
                }
                if (0 == $elementsModified && 0 == $errors) {
                    $this->addMessage('No changes.', self::MESSAGE_INFO);
                }
            } else {
                $rowId = $this->params()->fromPost('rowid');
                $formRows[$rowId]['baseId'] = $baseTranslation->getBaseId();
                try {
                    $success = $this->saveTranslationElement($formRows[$rowId]);

                    if (false == $success) {
                        $this->addMessage('No changes.', self::MESSAGE_INFO);
                    } else {
                        $this->addMessage(sprintf('Element saved successfully (element #%d)', $success), self::MESSAGE_SUCCESS);
                    }
                } catch(\Exception $e) {
                    $this->addMessage('Error saving element', self::MESSAGE_ERROR);
                }
            }
        }


        // prepare previous and next item
        $allBaseIds = $this->getResourceTranslationBase()->fetchIds();
        $currentKey = array_search($baseId, $allBaseIds);
        $previousKey = $currentKey - 1;
        $nextKey = $currentKey + 1;
        $maxKey = max(array_keys($allBaseIds));
        if (0 == $currentKey) {
            $previousKey = $maxKey;
        }
        if ($maxKey == $currentKey) {
            $nextKey = 0;
        }

        $translations = $this->getResourceTranslation()->fetchByBaseId($baseId);
        return new ViewModel(array(
            'supportedLocales'     => $this->getSupportedLocales(),
            'currentLocale'        => $this->_currentLocale,
            'messages'             => $this->_messages,
            'baseTranslation'      => $baseTranslation,
            'translations'         => $translations,
            'previousItemId'       => $allBaseIds[$previousKey],
            'nextItemId'           => $allBaseIds[$nextKey],
        ));
    }

    /**
     * save Translation element with given data
     *
     * @param array $element - Translation element data
     * @return int|false - id of saved element
     */
    protected function saveTranslationElement($element)
    {
        if (!array_key_exists('unclearTranslation', $element)) {
            $element['unclearTranslation'] = 0;
        }

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
