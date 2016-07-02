<?php


namespace Application\Resource;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Application\Model;

class TranslationBase extends Base
{

    protected $table = 'translation_base';


    public function fetchAll()
    {
        $resultSet = $this->select(function (Select $select) {
            $select->order('base_id ASC');
        });
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new Model\TranslationBase(array(
                'baseId'            => $row['base_id'],
                'translationFileId' => $row['translation_file_id'],
                'originSource'      => $row['origin_source'],
                'notInUse'          => $row['not_in_use'],
                'screenPath'        => $row['screen_path'],
            ));
            $entities[$row['base_id']] = $entity;
        }
        return $entities;
    }

    /**
     * @return array
     */
    public function fetchIds()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select($this->table);
        $select->columns(array('base_id'));
        $select->order(array('base_id' => 'ASC'));

        $statement  = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

        $ids = array();

        foreach ($resultSet as $row) {
            $ids[] = $row['base_id'];
        }

        return $ids;
    }

    public function getTranslationBase($baseId)
    {
        $row = $this->select(array('base_id' => (int) $baseId))->current();
        if (!$row)
            return false;

        $translationBase = new Model\TranslationBase(array(
            'baseId'            => $row->base_id,
            'translationFileId' => $row->translation_file_id,
            'originSource'      => $row->origin_source,
            'notInUse'          => $row->not_in_use,
            'screenPath'        => $row->screen_path,
        ));
        return $translationBase;
    }

    public function saveTranslation(Model\TranslationBase $translation)
    {
        $data = array(
            'base_id'             => $translation->getBaseId(),
            'translation_file_id' => $translation->getTranslationFileId(),
            'origin_source'       => $translation->getOriginSource(),
            'not_in_use'          => $translation->getNotInUse(),
            'screen_path'         => $translation->getScreenPath(),
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

    public function deleteTranslationBase($baseId)
    {
        return $this->delete(array('base_id' => (int) $baseId));
    }
}