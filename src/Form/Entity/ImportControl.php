<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 20/09/2019
 * Time: 09:41
 */

namespace Kookaburra\SystemAdmin\Form\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ImportControl
 * @package App\Form\Entity
 */
class ImportControl
{
    /**
     * @var string
     */
    private $mode = 'sync';

    /**
     * @var File
     */
    private $file;

    /**
     * @var string
     */
    private $fieldDelimiter = ',';

    /**
     * @var string
     */
    private $stringEnclosure = '"';

    /**
     * @var bool
     */
    private $ignoreErrors = false;

    /**
     * @var bool
     */
    private $syncField = false;

    /**
     * @var null|string
     */
    private $syncKey;

    /**
     * @var string|Collection|ImportColumn[]
     */
    private $columns;

    /**
     * @var null|string
     */
    private $csvData;

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Mode.
     *
     * @param string $mode
     * @return ImportControl
     */
    public function setMode(string $mode): ImportControl
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return null|File
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * File.
     *
     * @param null|File $file
     * @return ImportControl
     */
    public function setFile(?File $file): ImportControl
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldDelimiter(): string
    {
        return $this->fieldDelimiter;
    }

    /**
     * FieldDelimiter.
     *
     * @param string $fieldDelimiter
     * @return ImportControl
     */
    public function setFieldDelimiter(string $fieldDelimiter): ImportControl
    {
        $this->fieldDelimiter = $fieldDelimiter;
        return $this;
    }

    /**
     * @return string
     */
    public function getStringEnclosure(): string
    {
        return $this->stringEnclosure;
    }

    /**
     * StringEnclosure.
     *
     * @param string $stringEnclosure
     * @return ImportControl
     */
    public function setStringEnclosure(string $stringEnclosure): ImportControl
    {
        $this->stringEnclosure = $stringEnclosure;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreErrors(): bool
    {
        return $this->ignoreErrors = $this->ignoreErrors ? true : false;
    }

    /**
     * IgnoreErrors.
     *
     * @param bool|null $ignoreErrors
     * @return ImportControl
     */
    public function setIgnoreErrors(?bool $ignoreErrors): ImportControl
    {
        $this->ignoreErrors = $ignoreErrors ? true : false;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSyncField(): bool
    {
        return $this->syncField;
    }

    /**
     * SyncField.
     *
     * @param bool $syncField
     * @return ImportControl
     */
    public function setSyncField(?bool $syncField): ImportControl
    {
        $this->syncField = $syncField ? true : false;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSyncKey(): ?string
    {
        return $this->syncKey;
    }

    /**
     * SyncKey.
     *
     * @param string|null $syncKey
     * @return ImportControl
     */
    public function setSyncKey(?string $syncKey): ImportControl
    {
        $this->syncKey = $syncKey;
        return $this;
    }

    /**
     * getColumns
     * @return Collection|ImportColumn[]
     */
    public function getColumns()
    {
        return $this->columns = $this->columns ?: new ArrayCollection();
    }

    /**
     * setColumns
     * @param Collection|ImportColumn[] $columns
     * @return ImportControl
     */
    public function setColumns($columns): ImportControl
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * addColumn
     * @param ImportColumn $column
     * @return ImportControl
     */
    public function addColumn(ImportColumn $column): ImportControl
    {
        if ($this->getColumns()->contains($column))
            return $this;
        $this->getColumns()->add($column);
        return $this;
    }

    /**
     * getCsvData
     * @return string|null
     */
    public function getCsvData(): ?string
    {
        return $this->csvData;
    }

    /**
     * setCsvData
     * @param string|null $csvData
     * @return ImportControl
     */
    public function setCsvData(?string $csvData): ImportControl
    {
        $this->csvData = $csvData;
        return $this;
    }
}