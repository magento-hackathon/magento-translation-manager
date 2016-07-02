<?php


namespace Application\Resource;


use Zend\Db\Sql\Select;

class TranslationFile extends Base {

    protected $table = 'translation_file';

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
}