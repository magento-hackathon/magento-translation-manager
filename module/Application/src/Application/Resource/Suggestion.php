<?php


namespace Application\Resource;


use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Application\Model;

class Suggestion extends Base {

    protected $table = 'suggestion';

    public function fetchAll() {
        $resultSet = $this->select(function (Select $select) {
            $select->order('translation_id ASC');
        });

        $locales = array();
        foreach ($resultSet as $row) {
            $locales[] = $row->locale;
        }

        return $locales;
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