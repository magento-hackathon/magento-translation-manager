<?php

namespace Application\Model;

class Suggestion extends Base {

    /* @var int $_suggestionId */
    protected $_suggestionId = null;

    /* @var int $_translationId */
    protected $_translationId = null;

    /* @var string $_suggestedTranslation */
    protected $_suggestedTranslation = null;


    /**
     * @param string $suggestedTranslation
     * @return Suggestion $this
     */
    public function setSuggestedTranslation($suggestedTranslation)
    {
        $this->_suggestedTranslation = $suggestedTranslation;
        return $this;
    }

    /**
     * @return string
     */
    public function getSuggestedTranslation()
    {
        return $this->_suggestedTranslation;
    }

    /**
     * @param int $suggestionId
     * @return Suggestion $this
     */
    public function setSuggestionId($suggestionId)
    {
        $this->_suggestionId = $suggestionId;
        return $this;
    }

    /**
     * @return int
     */
    public function getSuggestionId()
    {
        return $this->_suggestionId;
    }

    /**
     * @param int $translationId
     * @return Suggestion $this
     */
    public function setTranslationId($translationId)
    {
        $this->_translationId = $translationId;
        return $this;
    }

    /**
     * @return int
     */
    public function getTranslationId()
    {
        return $this->_translationId;
    }
}