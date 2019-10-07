<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 7/10/2019
 * Time: 10:25
 */

namespace Kookaburra\SystemAdmin\Manager;

use App\Manager\Entity\PaginationColumn;
use App\Manager\Entity\PaginationRow;
use App\Manager\ReactPaginationInterface;
use App\Manager\ReactPaginationManager;

/**
 * Class ModulePagination
 * @package Kookaburra\SystemAdmin\Manager
 */
class ModulePagination extends ReactPaginationManager
{
    /**
     * execute
     * @return ReactPaginationInterface
     */
    public function execute(): ReactPaginationInterface
    {
        $row = new PaginationRow();
        $column = new PaginationColumn();
        $column->setLabel('Name');
        $column->setContentKey('name');
        $column->setSort(true);
        $column->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Status');
        $column->setContentKey('status');
        $column->setSort(true);
        $column->setClass('column');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Description');
        $column->setContentKey('description');
        $column->setSort(true);
        $column->setClass('column hidden sm:table-cell relative pr-4 cursor-pointer');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Type');
        $column->setContentKey('type');
        $column->setSort(true);
        $column->setClass('column hidden md:table-cell relative pr-4 cursor-pointer');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Version');
        $column->setContentKey('version');
        $column->setSort(true);
        $column->setClass('column hidden md:table-cell relative pr-4 cursor-pointer');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Author');
        $column->setContentKey('author');
        $column->setSort(true);
        $column->setClass('column hidden md:table-cell relative pr-4 cursor-pointer');
        $row->addColumn($column);

        $this->setRow($row);
        return $this;
    }

}