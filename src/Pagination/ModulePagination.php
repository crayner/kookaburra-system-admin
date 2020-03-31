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
 * Date: 7/10/2019
 * Time: 10:25
 */

namespace Kookaburra\SystemAdmin\Pagination;

use App\Manager\Entity\PaginationAction;
use App\Manager\Entity\PaginationColumn;
use App\Manager\Entity\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPaginationManager;
use App\Util\TranslationsHelper;

/**
 * Class ModulePagination
 * @package Kookaburra\SystemAdmin\Manager
 */
class ModulePagination extends AbstractPaginationManager
{
    /**
     * execute
     * @return PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        TranslationsHelper::setDomain('SystemAdmin');
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

        $action = new PaginationAction();
        $action->setTitle('Update')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-wrench fa-fw fa-1-5x text-gray-800 hover:text-orange-500')
            ->setRoute('system_admin__module_update')
            ->setDisplayWhen('updateRequired')
            ->setRouteParams(['upgrade' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-trash-alt fa-fw fa-1-5x text-gray-800 hover:text-red-500')
            ->setRoute('system_admin__module_delete')
            ->setDisplayWhen('isNotCore')
            ->setOnClick('areYouSure')
            ->setRouteParams(['delete' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }

}