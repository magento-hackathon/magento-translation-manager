<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;

class AdminController extends Base
{
    const EXPORT_PATH = 'export/';

    /**
     * admin dashboard
     * @return ViewModel
     */
    public function indexAction()
    {
        // prepare view
        $view =  new ViewModel(array(
            'translationFiles'     => $this->getResourceTranslationFile()->fetchAll(),
            'supportedLocales'     => $this->getSupportedLocales(),
        ));

        return $view;
    }

    /**
     * export language files as CSV data
     * HTTP-Param: translation_file
     * HTTP-Param: locale
     *
     * @return ViewModel
     */
    public function exportAction()
    {
        $downloadFiles = array();

        $exportFile = $this->params()->fromPost('translation_file');
        if (!$exportFile) {
            $exportFile = array();
        }
        $exportLocale = $this->params()->fromPost('locale');
        if (!$exportLocale) {
            $exportLocale = array();
        }

        $translationBase = $this->getResourceTranslationBase()->fetchAll();

        foreach ($exportLocale as $locale) {
            foreach ($exportFile as $fileName) {
                $translations = $this->getResourceTranslation()->fetchByLanguageAndFile($locale, $fileName);

                // prepare file to output in export folder
                $outputDirectory = 'public/' . self::EXPORT_PATH . "$locale/";
                if (!is_dir($outputDirectory)) {
                    mkdir($outputDirectory);
                }
                $outputFile = fopen($outputDirectory . $fileName, 'w');
                    foreach ($translations as $translation) {
                         /* @var $translation \Application\Model\Translation */
                         /* @var $base \Application\Model\TranslationBase */
                        $base = $translationBase[$translation['base_id']];
                        fputcsv(
                            $outputFile,
                            array($base->getOriginSource(), $translation['current_translation']),
                            ',', '"'
                        );
                    }
                fclose($outputFile);

                // store download filenames for template
                $downloadFiles[] = array(
                    'path'     => '/' . self::EXPORT_PATH . "$locale/" . $fileName,
                    'locale'   => $locale,
                    'filename' => $fileName,
                );
            }
        }


        return new ViewModel(array(
            'downloadFiles' => $downloadFiles, /* [path|locale|filename] => string */
        ));
    }

}