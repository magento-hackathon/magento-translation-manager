<?php
namespace Application\Resource;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Application\Model;

class TranslationBase extends Base
{

    protected $table = 'translation_base';

    /**
     * get all translation base
     *
     * @return Model\TranslationBase[] with index base_id
     */
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
     * get IDs of all translation base entries
     *
     * @return string[] - IDs of translation base entries
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

    /**
     * get translation base by id
     *
     * @param $baseId
     * @return Model\TranslationBase|bool - false on failure
     */
    public function getTranslationBase($baseId)
    {
        $row = $this->select(array('base_id' => (int) $baseId))->current();
        if (!$row) {
            return false;
        }

        $translationBase = new Model\TranslationBase(array(
            'baseId'            => $row->base_id,
            'translationFileId' => $row->translation_file_id,
            'originSource'      => $row->origin_source,
            'notInUse'          => $row->not_in_use,
            'screenPath'        => $row->screen_path,
        ));

        return $translationBase;
    }

    /**
     * save or update translation
     *
     * @param Model\TranslationBase $translationBase
     * @return bool|int - id of translation base on success, false on failure
     */
    public function saveTranslationBase(Model\TranslationBase $translationBase)
    {
        $data = array(
            'base_id'             => $translationBase->getBaseId(),
            'translation_file_id' => $translationBase->getTranslationFileId(),
            'origin_source'       => $translationBase->getOriginSource(),
            'not_in_use'          => $translationBase->getNotInUse(),
            'screen_path'         => $translationBase->getScreenPath(),
        );

        $baseId = (int) $translationBase->getBaseId();

        if ($baseId == 0) {
            // insert translation base
            if (!$this->insert($data))
                return false;
            return $this->getLastInsertValue();

        } elseif ($this->getTranslationBase($baseId)) {
            // update translation base
            if (!$this->update($data, array('base_id' => $baseId))) {
                return false;
            }
            return $baseId;

        } else {
            // unknown translation base
            return false;
        }
    }

    /**
     * delete translation_base by ID
     *
     * @param int $baseId
     * @return int - number of deleted translation base (should be one, because of PK)
     */
    public function deleteTranslationBase($baseId)
    {
        return $this->delete(array('base_id' => (int) $baseId));
    }
}