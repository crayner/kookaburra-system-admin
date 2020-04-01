<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 1/04/2020
 * Time: 09:48
 */

namespace Kookaburra\SystemAdmin\Pagination;


use App\Manager\AbstractPaginationManager;
use App\Manager\Entity\PaginationAction;
use App\Manager\Entity\PaginationColumn;
use App\Manager\Entity\PaginationFilter;
use App\Manager\Entity\PaginationRow;
use App\Manager\PaginationInterface;
use App\Util\TranslationsHelper;

class LanguagePagination extends AbstractPaginationManager
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
        $column->setLabel('Name')
            ->setContentKey('name')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Code')
            ->setContentKey('code')
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Active')
            ->setContentKey('active')
            ->setClass('column relative pr-4 cursor-pointer');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Status')
            ->setContentKey('status')
            ->setClass('column relative pr-4 cursor-pointer');
        $row->addColumn($column);


        $filter = new PaginationFilter();
        $filter->setName('Active: Yes')
            ->setGroup('Active')
            ->setContentKey('isActive')
            ->setValue(true);
        $row->addFilter($filter);

        $filter = new PaginationFilter();
        $filter->setName('Active: No')
            ->setGroup('Active')
            ->setContentKey('isActive')
            ->setValue(false);
        $row->addFilter($filter);

        /*$action = new PaginationAction();
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
        $row->addAction($action);*/

        $this->setRow($row);
        return $this;
    }
}