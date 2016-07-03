<?php

namespace Application\Controller;

use Application\Model\Suggestion;
use Application\Model\Translation;
use Zend\View\Model\ViewModel;

class IndexController extends Base
{
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
                    if (empty($row['suggestedTranslation'])) {
                        continue;
                    }
                    try {
                        $modified = $this->addSuggestion((int)$row['translationId'], $row['suggestedTranslation']);
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
                    $this->addMessage(sprintf('%d elements saved successfully', $elementsModified), self::MESSAGE_SUCCESS);
                }
                if (0 == $elementsModified && 0 == $errors) {
                    $this->addMessage('No changes.', self::MESSAGE_INFO);
                }
            } else {
                $rowId = $this->params()->fromPost('rowid');
                $jumpToRow = $rowId;
                try {
                    $success = false;
                    if (!empty($formRows[$rowId]['suggestedTranslation'])) {
                        $success = $this->addSuggestion((int)$formRows[$rowId]['translationId'], $formRows[$rowId]['suggestedTranslation']);
                    }

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
            'translationFiles'     => $this->getResourceTranslationFile()->fetchAll(),
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
                        if (empty($row['suggestedTranslation'])) {
                            continue;
                        }
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
                    $success = false;
                    if (!empty($formRows[$rowId]['suggestedTranslation'])) {
                        $success = $this->saveTranslationElement($formRows[$rowId]);
                    }

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
            'currentTranslationFile' => $this->getResourceTranslationFile()->getTranslationFile(
                $baseTranslation->getTranslationFileId()
            )->getFilename(),
            'messages'             => $this->_messages,
            'baseTranslation'      => $baseTranslation,
            'translations'         => $translations,
            'suggestions'          => $this->getResourceSuggestion()->fetchByTranslationId(
                $translations[$this->_currentLocale]->getTranslationId()
            ),
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
            $translation = $this->getResourceTranslation()->getTranslation($element['translation_id']);
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
     * add a new suggestion
     *
     * @param $translationId - ID of translation
     * @param $content - content of the suggestion
     * @return bool - was successfully saved
     */
    protected function addSuggestion($translationId, $content)
    {
        $result = $this->getResourceSuggestion()->saveSuggestion(new Suggestion(array(
            'suggestionId'         => null,
            'translationId'        => (int)$translationId,
            'suggestedTranslation' => $content,
        )));

        return boolval($result);
    }

}
