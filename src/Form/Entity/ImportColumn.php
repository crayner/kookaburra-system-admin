<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 22/09/2019
 * Time: 09:53
 */

namespace Kookaburra\SystemAdmin\Form\Entity;

/**
 * Class ImportColumn
 * @package App\Form\Entity
 */
class ImportColumn
{
    /**
     * @var integer
     */
    private $order;

    /**
     * @var mixed
     */
    private $text;

    /**
     * @var array
     */
    private $fieldType;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $label;

    /**
     * @var array
     */
    private $columnChoices = [];

    /**
     * @var array
     */
    private $flags;

    /**
     * @var string|null
     */
    private $textObjectName;

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order = intval($this->order) ?: 0;
    }

    /**
     * Order.
     *
     * @param int|null $order
     * @return ImportColumn
     */
    public function setOrder($order): ImportColumn
    {
        $this->order = intval($order);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Text.
     *
     * @param $text
     * @return ImportColumn
     */
    public function setText($text): ImportColumn
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldType(): array
    {
        return $this->fieldType = $this->fieldType ?: [];
    }

    /**
     * FieldType.
     *
     * @param array $fieldType
     * @return ImportColumn
     */
    public function setFieldType(array $fieldType): ImportColumn
    {
        $this->fieldType = $fieldType;
        return $this;
    }

    /**
     * @return null|array
     */
    public function getColumnChoices(): array
    {
        return $this->columnChoices = $this->columnChoices ?: [];
    }

    /**
     * setColumnChoices
     * @param array $choices1
     * @param array $choices2
     * @return ImportColumn
     */
    public function setColumnChoices(array $choices1, array $choices2 = []): ImportColumn
    {
        $columnChoices = [];
        $choices = $choices1 + $choices2;
        foreach($choices as $value=>$prompt)
            $columnChoices[intval($value)] = $prompt;

        $this->columnChoices = $columnChoices;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Name.
     *
     * @param string|null $name
     * @return ImportColumn
     */
    public function setName(?string $name): ImportColumn
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Label.
     *
     * @param string|null $label
     * @return ImportColumn
     */
    public function setLabel(?string $label): ImportColumn
    {
        $this->label = $label;
        return $this;
    }

    /**
     * getFlags
     * @return array
     */
    public function getFlags(): array
    {
        return $this->flags = $this->flags ?: [];
    }

    /**
     * setFlags
     * @param array $flags
     * @return ImportColumn
     */
    public function setFlags(array $flags): ImportColumn
    {
        $w = [];
        foreach($flags as $flag)
            $w[$flag] = true;
        $this->flags = $w;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTextObjectName(): ?string
    {
        return $this->textObjectName;
    }

    /**
     * TextObjectName.
     *
     * @param string|null $textObjectName
     * @return ImportColumn
     */
    public function setTextObjectName(?string $textObjectName): ImportColumn
    {
        $this->textObjectName = $textObjectName;
        return $this;
    }
}