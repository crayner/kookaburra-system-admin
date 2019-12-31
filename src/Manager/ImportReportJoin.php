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
 * Date: 23/09/2019
 * Time: 12:57
 */

namespace Kookaburra\SystemAdmin\Manager;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ImportReportJoin
 * @package App\Manager\Entity\SystemAdmin
 */
class ImportReportJoin
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $joinType;

    /**
     * @var string
     */
    private $targetTable;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var string|boolean
     */
    private $with;

    /**
     * @var bool
     */
    private $primary = false;

    /**
     * ImportReportJoin constructor.
     * @param string $name
     * @param array $details
     */
    public function __construct(string $name, array $details)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['table', 'alias']);
        $resolver->setDefaults(
            [
                'joinType' => 'join',
                'targetTable' => $name,
                'reference' => lcfirst($name),
                'with' => false,
                'primary' => false,
            ]
        );
        $resolver->setAllowedTypes('with', ['boolean','string']);
        $resolver->setAllowedTypes('primary', ['boolean']);
        $resolver->setAllowedValues('joinType',  ['join', 'leftJoin']);
        $details = $resolver->resolve($details);

        $this->setName($name);

        foreach($details as $name=>$value)
        {
            $name = 'set' . ucfirst($name);
            $this->$name($value);
        }
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Table.
     *
     * @param string $table
     * @return ImportReportJoin
     */
    public function setTable(string $table): ImportReportJoin
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Alias.
     *
     * @param string $alias
     * @return ImportReportJoin
     */
    public function setAlias(string $alias): ImportReportJoin
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getJoinType(): string
    {
        return $this->joinType;
    }

    /**
     * JoinType.
     *
     * @param string $joinType
     * @return ImportReportJoin
     */
    public function setJoinType(string $joinType): ImportReportJoin
    {
        $this->joinType = $joinType;
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetTable(): string
    {
        return $this->targetTable;
    }

    /**
     * TargetTable.
     *
     * @param string $targetTable
     * @return ImportReportJoin
     */
    public function setTargetTable(string $targetTable): ImportReportJoin
    {
        $this->targetTable = $targetTable;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Name.
     *
     * @param string $name
     * @return ImportReportJoin
     */
    public function setName(string $name): ImportReportJoin
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * Reference.
     *
     * @param string $reference
     * @return ImportReportJoin
     */
    public function setReference(string $reference): ImportReportJoin
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getWith()
    {
        return $this->with;
    }

    /**
     * With.
     *
     * @param bool|string $with
     * @return ImportReportJoin
     */
    public function setWith($with)
    {
        $this->with = $with;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * Primary.
     *
     * @param bool $primary
     * @return ImportReportJoin
     */
    public function setPrimary(bool $primary): ImportReportJoin
    {
        $this->primary = $primary;
        return $this;
    }
}