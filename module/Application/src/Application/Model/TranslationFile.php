<?php

namespace Application\Model;

class TranslationFile extends Base {

    /* @var int $_translationFileId */
    protected $_translationFileId = null;

    /* @var string $_filename */
    protected $_filename = null;

    /* @var string $_sourcePath */
    protected $_sourcePath = null;

    /* @var string $_destinationPath */
    protected $_destinationPath = null;

    /**
     * @param int $translationFileId
     * @return TranslationFile $this
     */
    public function setTranslationFileId($translationFileId) {
        $this->_translationFileId = (int)$translationFileId;
        return $this;
    }

    /**
     * @return int
     */
    public function getTranslationFileId() {
        return $this->_translationFileId;
    }

    /**
     * @param string $destinationPath
     * @return TranslationFile $this
     */
    public function setDestinationPath($destinationPath)
    {
        $this->_destinationPath = $destinationPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationPath()
    {
        return $this->_destinationPath;
    }

    /**
     * @param string $filename
     * @return TranslationFile $this
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * @param string $sourcePath
     * @return TranslationFile $this
     */
    public function setSourcePath($sourcePath)
    {
        $this->_sourcePath = $sourcePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourcePath()
    {
        return $this->_sourcePath;
    }
}