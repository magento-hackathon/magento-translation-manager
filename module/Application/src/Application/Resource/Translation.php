<?php


namespace Application\Resource;

use Zend\Db\Sql\Select;
use Application\Model;

class Translation extends Base {

    protected $table = 'translation';


    public function fetchAll() {
        $resultSet = $this->select(function (Select $select) {
            $select->order('created ASC');
        });
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new Model\Translation();
            $entity->setId($row->id)
                ->setNote($row->note)
                ->setCreated($row->created);
            $entities[] = $entity;
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
            'suggestedTranslation' => $row->suggestedTranslation,
            'unclearTranslation'   => $row->unclear_translation,
        ));
        return $translation;
    }

    public function saveTranslation(Model\Translation $translation) {
        $data = array(
            'id'                   => $translation->getId(),
            'baseId'               => $translation->getBaseId(),
            'locale'               => $translation->getLocale(),
            'currentTranslation'   => $translation->getCurrentTranslation(),
            'suggestedTranslation' => $translation->getSuggestedTranslation(),
            'unclearTranslation'   => $translation->getUnclearTranslation(),
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