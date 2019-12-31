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
 * Time: 11:50
 */

namespace Kookaburra\SystemAdmin\Manager;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ImportReportDetails
 * @package App\Manager\Entity\SystemAdmin
 */
class ImportReportDetails
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    private $modes;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $grouping = 'General';

    /**
     * @var string
     */
    private $category;

    /**
     * @var array
     */
    private $with = [];

    /**
     * ImportReportDetails constructor.
     * @param array $details
     */
    public function __construct(array $details)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(["alias", "modes", "name", "table", "title"]);
        $resolver->setDefaults([
            'grouping' => 'General',
            'category' => 'Kookaburra',
            'with' => [],
        ]);
        $details = $resolver->resolve($details);

        foreach($details as $name=>$value)
        {
            $name = 'set' . ucfirst($name);
            $this->$name($value);
        }
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
     * @return ImportReportDetails
     */
    public function setName(string $name): ImportReportDetails
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Title.
     *
     * @param string $title
     * @return ImportReportDetails
     */
    public function setTitle(string $title): ImportReportDetails
    {
        $this->title = $title;
        return $this;
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
     * @return ImportReportDetails
     */
    public function setTable(string $table): ImportReportDetails
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return array
     */
    public function getModes(): array
    {
        return $this->modes;
    }

    /**
     * Modes.
     *
     * @param array $modes
     * @return ImportReportDetails
     */
    public function setModes(array $modes): ImportReportDetails
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'update' => true, 'insert' => true, 'export' => true,
        ]);

        $this->modes = $resolver->resolve($modes);
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
     * @return ImportReportDetails
     */
    public function setAlias(string $alias): ImportReportDetails
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getGrouping(): string
    {
        return $this->grouping;
    }

    /**
     * Grouping.
     *
     * @param string $grouping
     * @return ImportReportDetails
     */
    public function setGrouping(string $grouping): ImportReportDetails
    {
        $this->grouping = $grouping;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Category.
     *
     * @param string $category
     * @return ImportReportDetails
     */
    public function setCategory(string $category): ImportReportDetails
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return array
     */
    public function getWith(): array
    {
        return $this->with;
    }

    /**
     * With.
     *
     * @param array $with
     * @return ImportReportDetails
     */
    public function setWith(array $with): ImportReportDetails
    {
        $this->with = $with;
        return $this;
    }
}