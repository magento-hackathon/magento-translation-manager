<?php
namespace Application\Resource;

use Application\Controller\IndexController;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Application\Model;

class Translation extends Base {

    protected $table = 'translation';

    /**
     * prepare array of all translations
     *
     * @param ResultSet $resultSet
     * @return Model\Translation[] with index translation_id
     */
    protected function _prepareCollection($resultSet)
    {
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new Model\Translation(array(
                'translationId'        => $row['translation_id'],
                'baseId'               => $row['base_id'],
                'locale'               => $row['locale'],
                'currentTranslation'   => $row['current_translation'],
                'unclearTranslation'   => $row['unclear_translation'],
            ));
            $entities[$row['translation_id']] = $entity;
        }
        return $entities;
    }

    /**
     * read all translations
     *
     * @return Model\Translation[]
     */
    public function fetchAll() {
        $resultSet = $this->select(function (Select $select) {
            $select->order('base_id ASC');
        });
        $entities = $this->_prepareCollection($resultSet);

        return $entities;
    }

    /**
     * count translations with given filter
     *
     * @param string $locale - locale to select
     * @param string|null $file - file to select (null = all files)
     * @param boolean $filterUnclear - filter only unclear translations
     * @return int - number of translations with this filter
     */
    public function countByLanguageAndFile($locale, $file = null, $filterUnclear = false)
    {
        // prepare base query
        $sql = new Sql($this->getAdapter());
        $select = $sql->select($this->table);
        $select = $this->prepareSqlByLanguageAndFile($select, $locale, $file, $filterUnclear);

        // add count
        $select->reset('columns')->columns(array('count' => new Expression('COUNT(*)')));

        $statement  = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

        $result = $resultSet->current();

        return (int)$result['count'];
    }


    /**
     * search all translations by given locale and file
     *
     * @param string $locale - locale to select
     * @param string|null $file - file to select (null = all files)
     * @param boolean $filterUnclear - filter only unclear translations
     * @param int|null $elementsPerPage - entries to show per page (null = all entries)
     * @param int $page - page to show
     * @return Model\Translation[]
     */
    public function fetchByLanguageAndFile($locale, $file = null, $filterUnclear = false, $elementsPerPage = IndexController::DEFAULT_ENTRIES_PER_PAGE, $page = 1)
    {
        $sql = new Sql($this->getAdapter());
        $select = $sql->select($this->table);
        $select = $this->prepareSqlByLanguageAndFile($select, $locale, $file, $filterUnclear);

        if (null !== $elementsPerPage) {
            // react to pagination
            $select->limit((int)$elementsPerPage)->offset(($page - 1) * $elementsPerPage);
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

    /**
     * prepare base SQL for grid filtered by language and file
     *
     * @param Select $select - empty Select object
     * @param string $locale - locale to filter
     * @param string|null $file - filename to filter
     * @param bool $filterUnclear - filter only unclear translations
     * @return Select - prepared query
     */
    protected function prepareSqlByLanguageAndFile($select, $locale, $file = null, $filterUnclear = false)
    {
        // we need table object for quoteinto

        $select->order('translation_id ASC');

        $joinCondition  = $this->table . '.base_id = translation_base.base_id ';
        $joinCondition .= " AND locale = " . $this->adapter->getPlatform()->quoteValue($locale) . ' OR locale IS NULL ';
        // quoteInto doesn't exist anymore and $this->adapter->getPlatform()->quoteValue() not working
        $select->join('translation_base', new Expression($joinCondition), '*', Select::JOIN_RIGHT);

        if (null != $file) {
            $select->join(
                'translation_file',
                'translation_base.translation_file_id = translation_file.translation_file_id',
                '*'
            );
            $select->where(array('filename' => $file));
        }

        if (true == $filterUnclear) {
            $select->where(array('unclear_translation' => 1));
        }

        return $select;
    }


    /**
     * get translated strings of base translation ordered by locale
     *
     * @param int $baseId
     * @return Model\Translation[] with index locale
     */
    public function fetchByBaseId($baseId)
    {
        $sql = new Sql($this->getAdapter());
        $select = $sql->select($this->table);
        $select->where(array('base_id' => $baseId));
        $select->order('locale ASC');

        $statement  = $sql->prepareStatementForSqlObject($select);

        $resultSet = $statement->execute();
        //$entities = $this->_prepareCollection($resultSet);
        $languages = array();
        foreach ($resultSet as $row) {
            $locale = $row['locale'];
            $languages[$locale] = new Model\Translation(array(
                'translationId'        => $row['translation_id'],
                'baseId'               => $row['base_id'],
                'locale'               => $row['locale'],
                'currentTranslation'   => $row['current_translation'],
                'unclearTranslation'   => $row['unclear_translation'],
            ));
        }
        $supportedLocale = new SupportedLocale($this->adapter);
        $supportedLocale = $supportedLocale->fetchAll();

        foreach ($supportedLocale as $locale) {
            if (!array_key_exists($locale, $languages)) {
                $languages[$locale] = new Model\Translation();
            }
        }

        return $languages;
    }

    /**
     * get translation by ID
     *
     * @param int $translationId
     * @return Model\Translation|bool - false if no Translation can be found
     */
    public function getTranslation($translationId) {
        $row = $this->select(array('translation_id' => (int) $translationId))->current();
        if (!$row) {
            return false;
        }

        $translation = new Model\Translation(array(
            'translationId'        => $row->translation_id,
            'baseId'               => $row->base_id,
            'locale'               => $row->locale,
            'currentTranslation'   => $row->current_translation,
            'unclearTranslation'   => $row->unclear_translation,
        ));

        return $translation;
    }

    /**
     * save or update translation
     *
     * @param Model\Translation $translation
     * @return bool|int - id of translation on success, false on failure
     */
    public function saveTranslation(Model\Translation $translation) {
        $data = array(
            'translation_id'        => $translation->getTranslationId(),
            'base_id'               => $translation->getBaseId(),
            'locale'                => $translation->getLocale(),
            'current_translation'   => $translation->getCurrentTranslation(),
            'unclear_translation'   => (int)$translation->getUnclearTranslation(),
        );

        $id = (int) $translation->getTranslationId();

        if ($id == 0) {
            // insert translation
            if (!$this->insert($data)) {
                return false;
            }
            return $this->getLastInsertValue();

        } elseif ($this->getTranslation($id)) {
            // update translation
            if (!$this->update($data, array('translation_id' => $id))) {
                return false;
            }
            return $id;

        } else {
            // unknown translation
            return false;
        }
    }

    /**
     * delete translation by id
     *
     * @param int $translationId
     * @return int - number of deleted translations (should be one, because of PK)
     */
    public function deleteTranslation($translationId) {
        return $this->delete(array('translation_id' => (int) $translationId));
    }

}