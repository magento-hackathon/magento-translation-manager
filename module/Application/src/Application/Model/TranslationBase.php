<?php

namespace Application\Model;

class TranslationBase extends Base {

    /* @var int $_baseId */
    protected $_baseId = null;
    /* @var string $_translationFileId */
    protected $_translationFileId = null;
    /* @var string $_originSource */
    protected $_originSource = null;
    /* @var boolean $_notInUse */
    protected $_notInUse = null;
    /* @var string $_screenPath */
    protected $_screenPath = null;


    public function getBaseId() {
        return $this->_baseId;
    }

    public function setBaseId($baseId) {
        $this->_baseId = (int)$baseId;
        return $this;
    }

    public function getTranslationFileId() {
        return $this->_translationFileId;
    }

    public function setTranslationFileId($translationFileId) {
        $this->_translationFileId = $translationFileId;
        return $this;
    }

    public function getOriginSource() {
        return $this->_originSource;
    }

    public function setOriginSource($originSource) {
        $this->_originSource = $originSource;
        return $this;
    }

    public function getNotInUse() {
        return $this->_notInUse;
    }

    public function setNotInUse($notInUse) {
        $this->_notInUse = $notInUse;
        return $this;
    }

    public function getScreenPath() {
        return $this->_screenPath;
    }

    public function setScreenPath($screenPath) {
        $this->_screenPath = $screenPath;
        return $this;
    }
}