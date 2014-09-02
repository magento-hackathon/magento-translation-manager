<?php


namespace Application\Resource;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Application\Model;

class TranslationBase extends Base {

    protected $table = 'translation_base';


    public function fetchAll() {
        $resultSet = $this->select(function (Select $select) {
            $select->order('base_id ASC');
        });
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new Model\TranslationBase(array(
                'baseId'          => $row['base_id'],
                'translationFile' => $row['translation_file'],
                'originSource'    => $row['origin_source'],
                'notInUse'        => $row['not_in_use'],
                'screenPath'      => $row['screen_path'],
            ));
            $entities[$row['base_id']] = $entity;
        }
        return $entities;
    }

    public function getTranslationBase($id) {
        $row = $this->select(array('id' => (int) $id))->current();
        if (!$row)
            return false;

        $translationBase = new Model\TranslationBase(array(
            'base_id'          => $row->base_id,
            'translation_file' => $row->translation_file,
            'origin_source'    => $row->origin_source,
            'not_in_use'       => $row->not_in_use,
            'screen_path'      => $row->screen_path,
        ));
        return $translationBase;
    }

    public function saveTranslation(Model\TranslationBase $translation) {
        $data = array(
            'base_id'          => $translation->getBaseId(),
            'translation_file' => $translation->getTranslationFile(),
            'origin_source'    => $translation->getOriginSource(),
            'not_in_use'       => $translation->getNotInUse(),
            'screen_path'      => $translation->getScreenPath(),
        );

        $baseId = (int) $translation->getBaseId();

        if ($baseId == 0) {
            if (!$this->insert($data))
                return false;
            return $this->getLastInsertValue();
        }
        elseif ($this->getTranslationBase($baseId)) {
            if (!$this->update($data, array('base_id' => $baseId)))
                return false;
            return $baseId;
        }
        else
            return false;
    }

    public function deleteTranslationBase($baseId) {
        return $this->delete(array('base_id' => (int) $baseId));
    }

    /**
     * select all possible translation files out of translation base table
     *
     * @return array of strings
     */
    public function getTranslationFileNames()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select($this->table);
        $select->columns(array('translation_file' => new Expression('DISTINCT(translation_file)')));

        $statement  = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

        $fileNames = array();

        foreach ($resultSet as $row) {
            $fileNames[] = $row['translation_file'];
        }

        return $fileNames;
    }

}