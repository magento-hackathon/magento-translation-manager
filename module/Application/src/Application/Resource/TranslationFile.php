<?php
namespace Application\Resource;

use Zend\Db\Sql\Select;
use Application\Model;

class TranslationFile extends Base {

    protected $table = 'translation_file';

    /**
     * get all file names
     *
     * @return string[] - file names
     */
    public function fetchAll() {
        $resultSet = $this->select(function (Select $select) {
            $select->order('filename ASC');
        });

        $filenames = array();
        foreach ($resultSet as $row) {
            $filenames[] = $row->filename;
        }

        return $filenames;
    }

    /**
     * get translation file name by ID
     *
     * @param int $translationFileId - ID of translation file
     * @return Model\TranslationFile|false when nothing exists
     */
    public function getTranslationFile($translationFileId) {
        $row = $this->select(array('translation_file_id' => (int) $translationFileId))->current();
        if (!$row) {
            return false;
        }

        $translationFile = new Model\TranslationFile(array(
            'translationFileId'    => $row->translation_file_id,
            'filename'             => $row->filename,
            'sourcePath'           => $row->source_path,
            'destinationPath'      => $row->destination_path,
        ));

        return $translationFile;
    }
}