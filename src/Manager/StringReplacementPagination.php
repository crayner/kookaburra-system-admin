<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 14/09/2019
 * Time: 11:43
 */

namespace Kookaburra\SystemAdmin\Manager;

use App\Manager\Entity\PaginationAction;
use App\Manager\Entity\PaginationColumn;
use App\Manager\Entity\PaginationRow;
use App\Manager\ReactPaginationInterface;
use App\Manager\ReactPaginationManager;

/**
 * Class StringReplacementPagination
 * @package App\Manager\SystemAdmin
 */
class StringReplacementPagination extends ReactPaginationManager
{
    /**
     * execute
     * @return ReactPaginationInterface
     */
    public function execute(): ReactPaginationInterface
    {
        $row = new PaginationRow();
        $column = new PaginationColumn();
        $column->setLabel('Original String');
        $column->setContentKey('original');
        $column->setSort(true);
        $column->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);
        $column = new PaginationColumn();
        $column->setLabel('Replacement String');
        $column->setContentKey('replacement');
        $column->setSort(true);
        $column->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);
        $column = new PaginationColumn();
        $column->setLabel('Mode');
        $column->setContentKey('mode');
        $column->setClass('column hidden md:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);
        $column = new PaginationColumn();
        $column->setLabel('Case Sensitive');
        $column->setContentKey('caseSensitive');
        $column->setClass('column hidden md:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);
        $column = new PaginationColumn();
        $column->setLabel('Priority');
        $column->setContentKey('priority');
        $column->setSort(true);
        $column->setClass('column hidden md:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('p-3 sm:p-0')
            ->setSpanClass('far fa-edit fa-fw fa-1-5x text-gray-700')
            ->setRoute('system_admin__string_replacement_edit')
            ->setRouteParams(['stringReplacement' => 'id']);
        $row->addAction($action);
        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-trash-alt fa-fw fa-1-5x text-gray-700')
            ->setRoute('system_admin__string_replacement_delete')
            ->setRouteParams(['stringReplacement' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }
}