<?php
namespace Application\Resource;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Application\Model;

class Suggestion extends Base {

    protected $table = 'suggestion';

    /**
     * prepare array of all suggestions
     *
     * @param ResultSet $resultSet
     * @return Model\Suggestion[] with index suggestion_id
     */
    protected function _prepareCollection($resultSet)
    {
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new Model\Suggestion(array(
                'suggestionId'         => $row['suggestion_id'],
                'translationId'        => $row['translation_id'],
                'suggestedTranslation' => $row['suggested_translation'],
            ));
            $entities[$row['suggestion_id']] = $entity;
        }
        return $entities;
    }

    /**
     * read all suggestions
     *
     * @return Model\Suggestion[]
     */
    public function fetchAll() {
        $resultSet = $this->select(function (Select $select) {
            $select->order('suggestion_id ASC');
        });
        $entities = $this->_prepareCollection($resultSet);

        return $entities;
    }

    /**
     * get suggestion by id
     *
     * @param int $suggestionId
     * @return Suggestion | false if not exists
     */
    public function fetchSuggestionById($suggestionId)
    {
        $sql = new Sql($this->getAdapter());
        $select = $sql->select($this->table);
        $select->where(array('suggestion_id' => $suggestionId));
        $select->order('suggestion_id ASC');

        $statement  = $sql->prepareStatementForSqlObject($select);

        $resultSet = $statement->execute();

        if (isset($resultSet[0])) {
            $row = $resultSet[0];
            return new Model\Suggestion(array(
                'suggestionId'         => $row['suggestion_id'],
                'translationId'        => $row['translation_id'],
                'suggestedTranslation' => $row['suggested_translation'],
            ));
        }

        return false;
    }

    /**
     * save or update suggestion
     *
     * @param Model\Suggestion $suggestion
     * @return bool|int - ID of suggestion on success, false on failure
     */
    public function saveSuggestion(Model\Suggestion $suggestion) {
        $data = array(
            'suggestion_id'         => $suggestion->getSuggestionId(),
            'translation_id'        => $suggestion->getTranslationId(),
            'suggested_translation' => $suggestion->getSuggestedTranslation(),
        );

        $id = (int) $suggestion->getSuggestionId();

        if ($id == 0) {
            // insert suggestion
            if (!$this->insert($data))
                return false;
            return $this->getLastInsertValue();

        } elseif ($this->getSuggestion($id)) {
            // update suggestion
            if (!$this->update($data, array('suggestion_id' => $id))) {
                return false;
            }
            return $id;

        } else {
            // unknown suggestion
            return false;
        }
    }

    /**
     * get suggestion by ID
     *
     * @param $suggestionId
     * @return Model\Suggestion|bool - false on failure
     */
    public function getSuggestion($suggestionId) {
        $row = $this->select(array('suggestion_id' => (int) $suggestionId))->current();
        if (!$row) {
            return false;
        }

        $suggestion = new Model\Suggestion(array(
            'suggestionId'            => $row->suggestion_id,
            'translationId'           => $row->translation_id,
            'suggestedTranslation'    => $row->suggested_translation,
        ));

        return $suggestion;
    }

    /**
     * delete suggestion by ID
     *
     * @param int $suggestionId
     * @return int - number of deleted suggestions (should be one, because of PK)
     */
    public function deleteSuggestion($suggestionId) {
        return $this->delete(array('suggestion_id' => (int) $suggestionId));
    }

    /**
     * get suggestions of a translation
     *
     * @param int $translationId
     * @return array of Suggestions (index = suggestion_id)
     */
    public function fetchByTranslationId($translationId)
    {
        $sql = new Sql($this->getAdapter());
        $select = $sql->select($this->table);
        $select->where(array('translation_id' => $translationId));
        $select->order('suggestion_id ASC');

        $statement  = $sql->prepareStatementForSqlObject($select);

        $resultSet = $statement->execute();

        $suggestions = array();
        foreach ($resultSet as $row) {
            $suggestionId = $row['suggestion_id'];
            $suggestions[$suggestionId] = new Model\Suggestion(array(
                'suggestionId'         => $row['suggestion_id'],
                'translationId'        => $row['translation_id'],
                'suggestedTranslation' => $row['suggested_translation'],
            ));
        }

        return $suggestions;
    }
}