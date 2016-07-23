<?php
namespace Application\Resource;

use Zend\Db\Sql\Select;

class SupportedLocale extends Base {

    protected $table = 'supported_locale';

    /**
     * read all possible locales
     *
     * @return string[] locale strings (e.g. de_DE)
     */
    public function fetchAll() {
        $resultSet = $this->select(function (Select $select) {
            $select->order('locale ASC');
        });

        $locales = array();
        foreach ($resultSet as $row) {
            $locales[] = $row->locale;
        }

        return $locales;
    }
}