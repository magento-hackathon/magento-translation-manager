<?php


namespace Application\Resource;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Application\Model;

class Translation extends Base {

    protected $table = 'translation';

    /**
     * @param ResultSet $resultSet
     * @return array of Model\Translation
     */
    protected function _prepareCollection($resultSet)
    {
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new Model\Translation(array(
                'id'                   => $row['id'],
                'baseId'               => $row['base_id'],
                'locale'               => $row['locale'],
                'currentTranslation'   => $row['current_translation'],
                'suggestedTranslation' => $row['suggested_translation'],
                'unclearTranslation'   => $row['unclear_translation'],
            ));
            $entities[$row['id']] = $entity;
        }
        return $entities;
    }


    public function fetchAll() {
        $resultSet = $this->select(function (Select $select) {
            $select->order('id ASC');
        });
        $entities = $this->_prepareCollection($resultSet);

        return $entities;
    }

    /**
     * @param string $locale - locale to select
     * @param string|null $file - file to select
     * @return array of Model\Translation
     */
    public function fetchByLanguageAndFile($locale, $file = null)
    {
        // we need table object for quoteinto

        $sql = new Sql($this->getAdapter());
        $select = $sql->select($this->table);
        $select->order('id ASC');

        $joinCondition  = $this->table . '.base_id = translation_base.base_id ';
        $joinCondition .= " AND locale = " . $this->adapter->getPlatform()->quoteValue($locale) . ' OR locale IS NULL ';
        // quoteInto doesn't exist anymore and $this->adapter->getPlatform()->quoteValue() not working
        $select->join('translation_base', new Expression($joinCondition), '*', Select::JOIN_RIGHT);

        if (null != $file) {
            $select->where(array('translation_file' => $file));
        }

        $statement  = $sql->prepareStatementForSqlObject($select);

        $resultSet = $statement->execute();
        //$entities = $this->_prepareCollection($resultSet);
        $entities = array();
        foreach ($resultSet as $row) {
            $entities[] = $row;
        }

        return $entities;
    }



    public function getTranslation($id) {
        $row = $this->select(array('id' => (int) $id))->current();
        if (!$row)
            return false;

        $translation = new Model\Translation(array(
            'id'                   => $row->id,
            'baseId'               => $row->base_id,
            'locale'               => $row->locale,
            'currentTranslation'   => $row->current_translation,
            'suggestedTranslation' => $row->suggested_translation,
            'unclearTranslation'   => $row->unclear_translation,
        ));
        return $translation;
    }

    public function saveTranslation(Model\Translation $translation) {
        $data = array(
            'id'                    => $translation->getId(),
            'base_id'               => $translation->getBaseId(),
            'locale'                => $translation->getLocale(),
            'current_translation'   => $translation->getCurrentTranslation(),
            'suggested_translation' => $translation->getSuggestedTranslation(),
            'unclear_translation'   => $translation->getUnclearTranslation(),
        );

        $id = (int) $translation->getId();

        if ($id == 0) {
            if (!$this->insert($data))
                return false;
            return $this->getLastInsertValue();
        }
        elseif ($this->getTranslation($id)) {
            if (!$this->update($data, array('id' => $id)))
                return false;
            return $id;
        }
        else
            return false;
    }

    public function deleteTranslation($id) {
        return $this->delete(array('id' => (int) $id));
    }

}